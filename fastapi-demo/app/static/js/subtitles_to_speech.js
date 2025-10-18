// --- ĐỊNH NGHĨA COMPONENT CHO ALPINE.JS ---
// Component này phải được định nghĩa ở ngoài để Alpine.js có thể tìm thấy khi khởi tạo.
document.addEventListener('alpine:init', () => {
    Alpine.data('audioPlayer', (src = '') => ({
        // Dữ liệu & Trạng thái
        isPlaying: false,
        isMuted: false,
        volume: 0.75,
        currentTime: 0,
        duration: 0,
        progress: 0,
        audioSrc: src,

        // Hàm khởi tạo khi player được tạo
        initPlayer() {
            if (this.$refs.audio) {
                this.duration = this.$refs.audio.duration || 0;
            }
        },
        // Bật/tắt phát nhạc
        togglePlay() {
            if (this.$refs.audio.paused) {
                this.$refs.audio.play();
                this.isPlaying = true;
            } else {
                this.$refs.audio.pause();
                this.isPlaying = false;
            }
        },
        // Cập nhật thanh tiến trình
        updateProgress() {
            this.currentTime = this.$refs.audio.currentTime;
            this.duration = this.$refs.audio.duration || 0;
            this.progress = this.duration > 0 ? (this.currentTime / this.duration) * 100 : 0;
        },
        // Tua đến vị trí bấm trên thanh tiến trình
        seek(event) {
            const rect = this.$refs.progressBar.getBoundingClientRect();
            const percent = (event.clientX - rect.left) / rect.width;
            this.$refs.audio.currentTime = percent * this.duration;
        },
        // Bật/tắt tiếng
        toggleMute() {
            this.isMuted = !this.isMuted;
            this.$refs.audio.muted = this.isMuted;
        },
        // Cập nhật âm lượng
        updateVolume() {
            this.$refs.audio.volume = this.volume;
            this.isMuted = this.volume == 0;
        },
        // Định dạng thời gian (ví dụ: 1:05)
        formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        },
        // Xử lý khi nhạc phát xong
        onAudioEnd() {
            this.isPlaying = false;
            this.currentTime = 0; // Tua về đầu
        }
    }));
});

// --- LOGIC CHÍNH CỦA TRANG ---
document.addEventListener('DOMContentLoaded', () => {
    // --- Lấy và lưu các phần tử DOM ---
    const dom = {
        form: document.getElementById('ttsForm'),
        textInput: document.getElementById('tts-text'),
        charCount: document.getElementById('char-count'),
        voiceSelector: document.getElementById('voice-selector'),
        voiceSamples: document.getElementById('voice-samples'),
        submitButton: document.getElementById('submit-button'),
        statusEl: document.getElementById('status'),
        resultContainer: document.getElementById('result-container'),
        saveOriginalBtn: document.getElementById('save-original-btn'),
        tabUpload: document.getElementById('tab-upload'),
        tabPaste: document.getElementById('tab-paste'),
        uploadPanel: document.getElementById('upload-panel'),
        pastePanel: document.getElementById('paste-panel'),
        fileInput: document.getElementById('subtitleFile'),
        uploadArea: document.getElementById('file-upload-area'),
        fileNameDisplay: document.getElementById('file-name-display'),
    };

    // --- Dữ liệu & Trạng thái ---
    const voices = [
        { id: 'nova', name: 'Nova', gender: 'Nữ', accent: 'Giọng Bắc', avatar: 'https://placehold.co/40x40/a78bfa/FFFFFF?text=N' },
        { id: 'alloy', name: 'Alloy', gender: 'Nam', accent: 'Giọng Nam', avatar: 'https://placehold.co/40x40/3b82f6/FFFFFF?text=A' },
        { id: 'fable', name: 'Fable', gender: 'Nam', accent: 'Kể chuyện', avatar: 'https://placehold.co/40x40/fb923c/FFFFFF?text=F' },
        { id: 'shimmer', name: 'Shimmer', gender: 'Nữ', accent: 'Truyền cảm', avatar: 'https://placehold.co/40x40/f472b6/FFFFFF?text=S' },
    ];
    let selectedVoice = voices[0].id;
    let uploadedFile = null;
    let sampleAudioCache = {};
    const MAX_CHARS = 4096;

    // --- Các hàm hiển thị giao diện ---
    function renderVoiceSelector() {
        dom.voiceSelector.innerHTML = voices.map(voice => `
            <div class="voice-option p-3 border rounded-lg cursor-pointer transition ${voice.id === selectedVoice ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 bg-white'}" data-voice-id="${voice.id}">
                <div class="flex items-center gap-3"><img src="${voice.avatar}" class="w-8 h-8 rounded-full"><div><p class="font-semibold text-gray-800">${voice.name}</p><p class="text-xs text-gray-500">${voice.gender} - ${voice.accent}</p></div></div>
            </div>`).join('');
    }

    function renderVoiceSamples() {
        dom.voiceSamples.innerHTML = voices.map(voice => `
            <div class="flex items-center justify-between py-1"><span class="text-sm font-medium text-gray-700">${voice.name} (${voice.gender})</span><button type="button" class="play-sample-btn p-2 rounded-full hover:bg-gray-200 transition" data-voice-id="${voice.id}" title="Nghe thử giọng ${voice.name}"><i class="fa-solid fa-play text-gray-600"></i></button></div>`).join('');
    }

    // --- Các hàm xử lý sự kiện ---
    dom.textInput.addEventListener('input', () => {
        const count = dom.textInput.value.length;
        dom.charCount.textContent = count;
        dom.charCount.classList.toggle('text-red-500', count > MAX_CHARS);
    });

    dom.voiceSelector.addEventListener('click', (e) => {
        const option = e.target.closest('.voice-option');
        if (option) { selectedVoice = option.dataset.voiceId; renderVoiceSelector(); }
    });

    dom.voiceSamples.addEventListener('click', (e) => {
        const playBtn = e.target.closest('.play-sample-btn');
        if (playBtn) playSample(playBtn);
    });

    dom.form.addEventListener('submit', handleFormSubmit);
    dom.saveOriginalBtn?.addEventListener('click', saveOriginalContent);
    dom.tabUpload.addEventListener('click', () => switchTab('upload'));
    dom.tabPaste.addEventListener('click', () => switchTab('paste'));
    dom.uploadArea.addEventListener('click', () => dom.fileInput.click());
    dom.fileInput.addEventListener('change', () => handleFileSelect(dom.fileInput.files));
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => dom.uploadArea.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }, false));
    ['dragenter', 'dragover'].forEach(ev => dom.uploadArea.addEventListener(ev, () => dom.uploadArea.classList.add('border-blue-500'), false));
    ['dragleave', 'drop'].forEach(ev => dom.uploadArea.addEventListener(ev, () => dom.uploadArea.classList.remove('border-blue-500'), false));
    dom.uploadArea.addEventListener('drop', (e) => handleFileSelect(e.dataTransfer.files), false);

    // --- Các hàm xử lý logic chính ---
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
    }

    function handleFileSelect(files) {
        if (files.length > 0) {
            uploadedFile = files[0];
            dom.fileNameDisplay.textContent = uploadedFile.name;
            dom.fileNameDisplay.classList.add('font-semibold', 'text-blue-700');
            const reader = new FileReader();
            reader.onload = (e) => { dom.textInput.value = e.target.result; dom.textInput.dispatchEvent(new Event('input')); };
            reader.readAsText(uploadedFile);
        }
    }

    async function playSample(button) {
        const voiceId = button.dataset.voiceId;
        const icon = button.querySelector('i');
        const originalIconClass = icon.className;
        icon.className = 'fa-solid fa-spinner animate-spin text-blue-600'; button.disabled = true;
        try {
            if (sampleAudioCache[voiceId]) { new Audio(sampleAudioCache[voiceId]).play(); } 
            else {
                const res = await fetch('/subtitles-to-speech/api/enqueue', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ text: "Xin chào, đây là giọng đọc mẫu của tôi.", voice: voiceId }) });
                if (!res.ok) throw new Error('Failed to generate sample.');
                const job = await res.json();
                await pollSampleJob(job.meta_key, (audioUrl) => { sampleAudioCache[voiceId] = audioUrl; new Audio(audioUrl).play(); });
            }
        } catch (error) { handleError('Không thể tạo âm thanh mẫu.'); } 
        finally { setTimeout(() => { icon.className = originalIconClass; button.disabled = false; }, 2500); }
    }
    
    async function handleFormSubmit(e) {
        e.preventDefault();
        const text = dom.textInput.value.trim();
        if (!text) { setStatus('Vui lòng nhập văn bản.', 'error'); return; }
        if (text.length > MAX_CHARS) { setStatus(`Văn bản quá dài. Dưới ${MAX_CHARS} ký tự.`, 'error'); return; }
        resetUI(); setStatus('Đang gửi yêu cầu đến AI...', 'loading'); dom.submitButton.disabled = true;
        try {
            const formData = new FormData();
            formData.append('text', text);
            formData.append('voice', selectedVoice);
            const res = await fetch('/subtitles-to-speech/api/enqueue', { method: 'POST', body: formData });
            if (!res.ok) { const err = await res.json(); throw new Error(err.detail || 'Tạo job thất bại.'); }
            const job = await res.json();
            pollJob(job.meta_key);
        } catch (error) { handleError(error.message); }
    }

    function saveOriginalContent() {
        const content = dom.textInput.value;
        if (!content.trim()) return;
        const isSrt = content.includes('-->');
        const fileExtension = isSrt ? 'srt' : 'txt';
        const filename = `subtitle_original.${fileExtension}`;
        const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none'; a.href = url; a.download = filename;
        document.body.appendChild(a); a.click();
        window.URL.revokeObjectURL(url); a.remove();
    }
    
    // --- API Polling ---
    function pollJob(metaKey) {
        const intervalId = setInterval(async () => {
            try {
                const res = await fetch(`/api/job/${metaKey}`);
                if (!res.ok) throw new Error("Job không tồn tại.");
                const meta = await res.json();
                setStatus(`AI đang xử lý giọng nói...`, 'loading');
                if (meta.status === "finished" || meta.status === "error") {
                    clearInterval(intervalId);
                    dom.submitButton.disabled = false;
                    if (meta.status === "finished") {
                        setStatus("Tạo file âm thanh hoàn tất!", "success");
                        const resultUrl = `/api/job/${metaKey}/result`;
                        // Tạo và chèn player mới
                        dom.resultContainer.innerHTML = createPlayerHTML(resultUrl);
                        dom.resultContainer.classList.remove('hidden');
                    } else { handleError(meta.error || 'Lỗi không xác định'); }
                }
            } catch (error) { handleError(error.message); clearInterval(intervalId); }
        }, 2000);
    }
    
    function pollSampleJob(metaKey, onFinish) {
        return new Promise((resolve, reject) => {
            const intervalId = setInterval(async () => {
                try {
                    const res = await fetch(`/api/job/${metaKey}`);
                    if (!res.ok) throw new Error("Job mẫu không tồn tại.");
                    const meta = await res.json();
                    if (meta.status === "finished") {
                        clearInterval(intervalId);
                        onFinish(`/api/job/${metaKey}/result`);
                        resolve();
                    } else if (meta.status === "error") { throw new Error("Tạo mẫu thất bại."); }
                } catch (error) { clearInterval(intervalId); reject(error); }
            }, 1500);
        });
    }

    function createPlayerHTML(src) {
        return `
            <div x-data="audioPlayer('${src}')" class="bg-white rounded-xl shadow-lg p-6">
                <audio x-ref="audio" @timeupdate="updateProgress" @loadedmetadata="initPlayer" @ended="onAudioEnd" preload="metadata" class="hidden">
                    <source src="${src}" type="audio/mp3">
                </audio>
                <div class="w-full mb-2"><div x-ref="progressBar" @click="seek($event)" class="relative w-full h-2 bg-gray-200 rounded-full cursor-pointer group"><div class="absolute top-0 left-0 h-full bg-blue-500 rounded-full" :style="\`width: \${progress}%\`"></div><div class="absolute top-1/2 w-4 h-4 bg-blue-500 rounded-full transform -translate-x-1/2 -translate-y-1/2 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity" :style="\`left: \${progress}%\`"></div></div></div>
                <div class="flex justify-between text-gray-500 text-sm mb-4"><span x-text="formatTime(currentTime)">0:00</span><span x-text="formatTime(duration)">0:00</span></div>
                <div class="flex items-center justify-between">
                    <button type="button" @click="togglePlay" class="text-white bg-blue-600 hover:bg-blue-500 transition-colors rounded-full w-16 h-16 flex items-center justify-center shadow-lg"><svg x-show="!isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 ml-1"><path fill-rule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.648c1.295.742 1.295 2.545 0 3.286L7.279 20.99c-1.25.717-2.779-.217-2.779-1.643V5.653z" clip-rule="evenodd" /></svg><svg x-show="isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8" style="display: none;"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 00-.75.75v12a.75.75 0 00.75.75h.75a.75.75 0 00.75-.75V6a.75.75 0 00-.75-.75H6.75zm5.25 0a.75.75 0 00-.75.75v12a.75.75 0 00.75.75h.75a.75.75 0 00.75-.75V6a.75.75 0 00-.75-.75h-.75z" clip-rule="evenodd" /></svg></button>
                    <div class="flex items-center space-x-2"><button type="button" @click="toggleMute" class="text-gray-500 hover:text-blue-500 transition-colors"><svg x-show="!isMuted && volume > 0" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" /></svg><svg x-show="isMuted || volume == 0" class="w-6 h-6" style="display: none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" /></svg></button><input type="range" min="0" max="1" step="0.01" x-model="volume" @input="updateVolume" class="w-24 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"></div>
                    <a href="${src}" class="text-gray-500 hover:text-blue-500 transition-colors" title="Tải về file MP3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-.53 14.03a.75.75 0 001.06 0l3-3a.75.75 0 10-1.06-1.06l-1.72 1.72V8.25a.75.75 0 00-1.5 0v5.69l-1.72-1.72a.75.75 0 00-1.06 1.06l3 3z" clip-rule="evenodd" /></svg></a>
                </div>
            </div>`;
    }

    // --- UI Utility ---
    function setStatus(message, type = "info") { /* ... */ }
    function handleError(message) { setStatus(message, 'error'); dom.submitButton.disabled = false; }
    function resetUI() { dom.resultContainer.innerHTML = ''; dom.resultContainer.classList.add('hidden'); setStatus(''); }

    // --- Initial Load ---
    renderVoiceSelector();
    renderVoiceSamples();
    switchTab('paste');
});