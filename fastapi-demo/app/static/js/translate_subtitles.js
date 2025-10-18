/**
 * Logic for the Subtitle Translation page with all features.
 */
document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const dom = {
        form: document.getElementById('translateForm'),
        fileInput: document.getElementById('subtitleFile'),
        uploadArea: document.getElementById('file-upload-area'),
        fileNameDisplay: document.getElementById('file-name-display'),
        subtitleText: document.getElementById('subtitleText'),
        sourceLangSelect: document.getElementById('source_lang'),
        targetLangSelect: document.getElementById('target_lang'),
        outputFormatSelect: document.getElementById('output_format'),
        rewriteCheckbox: document.getElementById('rewrite-checkbox'),
        submitButton: document.getElementById('submit-button'),
        statusEl: document.getElementById('status'),
        resultContainer: document.getElementById('result-container'),
        downloadBtn: document.getElementById('download-btn'),
        statsContainer: document.getElementById('stats-container'),
        lineCountEl: document.getElementById('line-count'),
        wordCountEl: document.getElementById('word-count'),
        detectedLangEl: document.getElementById('detected-lang'),
        translatedOutputContainer: document.getElementById('translated-output-container'),
        translatedOutputText: document.getElementById('translated-output-text'),
        copyButton: document.getElementById('copy-button'),
        tabUpload: document.getElementById('tab-upload'),
        tabPaste: document.getElementById('tab-paste'),
        uploadPanel: document.getElementById('upload-panel'),
        pastePanel: document.getElementById('paste-panel'),
    };

    let uploadedFile = null;
    let analysisTimeout = null;

    // --- Tab Switching Logic ---
    function switchTab(activeTab) {
        const activeClasses = 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600';
        const inactiveClasses = 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
        if (activeTab === 'upload') {
            dom.tabUpload.className = activeClasses; dom.tabPaste.className = inactiveClasses;
            dom.uploadPanel.classList.remove('hidden'); dom.pastePanel.classList.add('hidden');
        } else {
            dom.tabPaste.className = activeClasses; dom.tabUpload.className = inactiveClasses;
            dom.pastePanel.classList.remove('hidden'); dom.uploadPanel.classList.add('hidden');
        }
        analyzeContent(activeTab === 'upload' ? (uploadedFile ? uploadedFile.content : '') : dom.subtitleText.value);
    }
    dom.tabUpload.addEventListener('click', () => switchTab('upload'));
    dom.tabPaste.addEventListener('click', () => switchTab('paste'));

    // --- File Upload Logic ---
    dom.uploadArea.addEventListener('click', () => dom.fileInput.click());
    dom.fileInput.addEventListener('change', () => handleFileSelect(dom.fileInput.files));
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => dom.uploadArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false));
    ['dragenter', 'dragover'].forEach(eventName => dom.uploadArea.addEventListener(eventName, () => dom.uploadArea.classList.add('border-blue-500'), false));
    ['dragleave', 'drop'].forEach(eventName => dom.uploadArea.addEventListener(eventName, () => dom.uploadArea.classList.remove('border-blue-500'), false));
    dom.uploadArea.addEventListener('drop', (e) => handleFileSelect(e.dataTransfer.files), false);

    function handleFileSelect(files) {
        if (files.length > 0) {
            uploadedFile = files[0];
            dom.fileNameDisplay.textContent = uploadedFile.name;
            dom.fileNameDisplay.classList.add('font-semibold', 'text-blue-700');
            const reader = new FileReader();
            reader.onload = (e) => { uploadedFile.content = e.target.result; analyzeContent(e.target.result); };
            reader.readAsText(uploadedFile);
        }
    }

    // --- Text Area Logic ---
    dom.subtitleText.addEventListener('input', () => {
        clearTimeout(analysisTimeout);
        analysisTimeout = setTimeout(() => { analyzeContent(dom.subtitleText.value); }, 500);
    });
    
    // --- Lõi phân tích ---
    async function analyzeContent(content) {
        if (!content || !content.trim()) { dom.statsContainer.classList.add('hidden'); return; }
        const lines = content.split('\n').map(line => line.trim());
        const textOnlyLines = lines.filter(line => line && isNaN(line) && !line.includes('-->'));
        const wordCount = textOnlyLines.join(' ').split(/\s+/).filter(Boolean).length;
        dom.lineCountEl.textContent = textOnlyLines.length;
        dom.wordCountEl.textContent = wordCount;
        dom.statsContainer.classList.remove('hidden');
        dom.detectedLangEl.textContent = '...';
        const textSnippet = textOnlyLines.slice(0, 10).join(' ');
        try {
            const response = await fetch('/translate-subtitles/api/detect-language', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ text: textSnippet }) });
            if (!response.ok) throw new Error('Detection failed');
            const data = await response.json();
            const langCode = data.language || 'N/A';
            dom.detectedLangEl.textContent = langCode;
            const optionExists = Array.from(dom.sourceLangSelect.options).some(opt => opt.value === langCode);
            dom.sourceLangSelect.value = optionExists ? langCode : 'auto';
        } catch (error) { dom.detectedLangEl.textContent = 'N/A'; dom.sourceLangSelect.value = 'auto'; }
    }

    // --- Form Submission Logic ---
    dom.form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const textContent = dom.subtitleText.value.trim();
        if (!uploadedFile && !textContent) { setStatus('Vui lòng chọn file hoặc dán nội dung.', 'error'); return; }
        const formData = new FormData();
        if (dom.pastePanel.classList.contains('hidden')) { formData.append('file', uploadedFile); } 
        else { formData.append('subtitle_text', textContent); }
        formData.append('source_lang', dom.sourceLangSelect.value);
        formData.append('target_lang', dom.targetLangSelect.value);
        formData.append('rewrite', dom.rewriteCheckbox.checked);
        formData.append('output_format', dom.outputFormatSelect.value);
        resetUI();
        setStatus('Đang gửi yêu cầu...', 'loading');
        dom.submitButton.disabled = true;
        try {
            const response = await fetch('/translate-subtitles/api/enqueue', { method: 'POST', body: formData });
            if (!response.ok) { const err = await response.json(); throw new Error(err.detail || 'Tạo job thất bại.'); }
            const job = await response.json();
            pollJob(job.meta_key);
        } catch (error) { handleError(error.message); }
    });
    
    dom.copyButton.addEventListener('click', () => {
        const textToCopy = dom.translatedOutputText.value;
        if (!textToCopy) return;

        // Ưu tiên dùng API Clipboard hiện đại
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                updateCopyButtonState('Đã sao chép!', true);
            }).catch(err => {
                console.warn(' navigator.clipboard failed, trying fallback...');
                fallbackCopyText(textToCopy); // Chuyển sang phương pháp cũ nếu thất bại
            });
        } else {
            // Dùng phương pháp cũ cho các trình duyệt hoặc môi trường không hỗ trợ
            fallbackCopyText(textToCopy);
        }
    });

    /**
     * Phương pháp sao chép cũ hơn, tương thích rộng rãi.
     */
    function fallbackCopyText(text) {
        dom.translatedOutputText.select(); // Chọn toàn bộ text trong textarea
        dom.translatedOutputText.setSelectionRange(0, 99999); // Dành cho thiết bị di động
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                updateCopyButtonState('Đã sao chép!', true);
            } else {
                updateCopyButtonState('Lỗi sao chép', false);
            }
        } catch (err) {
            console.error('Fallback copy failed', err);
            updateCopyButtonState('Lỗi sao chép', false);
        }
        window.getSelection().removeAllRanges(); // Bỏ chọn text
    }
    function updateCopyButtonState(text, success) {
        const iconClass = success ? 'fa-solid fa-check' : 'fa-solid fa-times';
        const colorClass = success ? 'text-green-600' : 'text-red-600';
        const originalContent = '<i class="fa-solid fa-copy mr-1"></i><span>Sao chép</span>';

        dom.copyButton.innerHTML = `<i class="${iconClass} ${colorClass} mr-1"></i> <span class="${colorClass}">${text}</span>`;
        dom.copyButton.disabled = true;

        setTimeout(() => {
            dom.copyButton.innerHTML = originalContent;
            dom.copyButton.disabled = false;
        }, 2000);
    }

    // --- API Polling & Result Handling ---
    function pollJob(metaKey) {
        const intervalId = setInterval(async () => {
            try {
                const res = await fetch(`/api/job/${metaKey}`);
                if (!res.ok) throw new Error("Job không tồn tại.");
                const meta = await res.json();
                setStatus(`Đang dịch phụ đề...`, 'loading');
                if (meta.status === "finished" || meta.status === "error") {
                    clearInterval(intervalId);
                    dom.submitButton.disabled = false;
                    if (meta.status === "finished") {
                        setStatus("Dịch thuật hoàn tất!", "success");
                        dom.downloadBtn.href = `/api/job/${metaKey}/result`;
                        dom.resultContainer.classList.remove('hidden');
                        fetch(dom.downloadBtn.href)
                            .then(response => response.text())
                            .then(content => {
                                dom.translatedOutputText.value = content;
                                dom.translatedOutputContainer.classList.remove('hidden');
                            });
                    } else { handleError(meta.error || 'Lỗi không xác định'); }
                }
            } catch (error) { handleError(error.message); clearInterval(intervalId); }
        }, 2000);
    }

    // --- UI Utility Functions ---
    function setStatus(message, type = "info") {
        if (!dom.statusEl) return;
        if (!message) { dom.statusEl.innerHTML = ""; return; }
        let iconHtml, colors;
        const flexClasses = "flex items-center justify-center gap-3";
        switch (type) {
            case 'loading':
                iconHtml = `<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                colors = 'bg-blue-50 border-blue-200 text-blue-800'; break;
            case 'success':
                iconHtml = `<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>`;
                colors = 'bg-green-50 border-green-200 text-green-800'; break;
            case 'error':
                iconHtml = `<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>`;
                colors = 'bg-red-50 border-red-200 text-red-800'; break;
            default: iconHtml = ''; colors = 'bg-gray-50 border-gray-200 text-gray-800';
        }
        dom.statusEl.innerHTML = `<div class="${flexClasses} ${colors} text-sm font-medium px-4 py-3 rounded-lg border"><span>${iconHtml}</span><span>${message}</span></div>`;
    }
    function handleError(message) { setStatus(message, 'error'); dom.submitButton.disabled = false; }
    function resetUI() {
        dom.resultContainer.classList.add('hidden');
        dom.translatedOutputContainer.classList.add('hidden');
        dom.translatedOutputText.value = '';
        setStatus('');
    }
});