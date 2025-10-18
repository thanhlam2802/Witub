
const VideoDownloaderApp = {
    // --- Cấu hình ---
    config: {
        pollInterval: 1000, // Kiểm tra nhanh hơn để cập nhật % mượt hơn
        supportedPlatforms: [
            // Mạng xã hội & Video phổ biến nhất
            { name: 'YouTube', icon: 'fab fa-youtube text-red-500' },
            { name: 'Facebook', icon: 'fab fa-facebook text-blue-600' },
            { name: 'TikTok', icon: 'fab fa-tiktok text-black' },
            { name: 'Instagram', icon: 'fab fa-instagram text-pink-500' },
            { name: 'X (Twitter)', icon: 'fab fa-twitter text-blue-400' },
            
            { name: 'Reddit', icon: 'fab fa-reddit-alien text-orange-600' },
            { name: 'Pinterest', icon: 'fab fa-pinterest text-red-700' },
            { name: 'Threads', icon: 'fab fa-threads text-black' },
            { name: 'Behance', icon: 'fab fa-behance-square text-blue-700' },
            // Nền tảng Video chuyên dụng
            { name: 'Vimeo', icon: 'fab fa-vimeo-v text-cyan-500' },
            { name: 'Dailymotion', icon: 'fas fa-play-circle text-gray-800' },
            { name: 'Twitch', icon: 'fab fa-twitch text-purple-600' },
            { name: 'Bilibili', icon: 'fas fa-tv text-sky-500' },
            { name: 'Rumble', icon: 'fas fa-video text-green-700' },
            { name: 'Odysee', icon: 'fas fa-rocket text-red-500' },
            
            // Nền tảng Âm nhạc & Podcast
            { name: 'SoundCloud', icon: 'fab fa-soundcloud text-orange-500' },
            { name: 'Bandcamp', icon: 'fab fa-bandcamp text-teal-500' },
            { name: 'Mixcloud', icon: 'fab fa-mixcloud text-blue-800' },
            
            // Nền tảng khu vực Châu Á
            { name: 'Douyin', icon: 'fab fa-tiktok text-black' },
            { name: 'VK', icon: 'fab fa-vk text-sky-600' },
            { name: 'Weibo', icon: 'fab fa-weibo text-orange-500' },

            // Tin tức & Giáo dục
            { name: 'BBC', icon: 'fas fa-newspaper text-black' },
            { name: 'CNN', icon: 'fas fa-tv text-red-600' },
            { name: 'TED', icon: 'fas fa-microphone-alt text-red-600' },
            { name: 'ESPN', icon: 'fas fa-basketball-ball text-red-600' },
            
            // Các nền tảng khác
            { name: 'Imgur', icon: 'fas fa-image text-green-400' },
            { name: 'IMDb', icon: 'fab fa-imdb text-yellow-500' },
            { name: 'Steam', icon: 'fab fa-steam text-gray-800' },
        ]
    },

    // --- Trạng thái ---
    state: { isPolling: false, currentURL: '' },

    /**
     * Khởi chạy ứng dụng
     */
    init() {
        this.cacheDOMElements();
        this.bindEvents();
        this.renderSupportedPlatforms();
    },

    /**
     * Lấy và lưu các phần tử DOM
     */
    cacheDOMElements() {
        this.dom = {
            form: document.getElementById("getInfoForm"),
            urlInput: document.getElementById("urlInput"),
            submitButton: document.getElementById("submit-button"),
            statusEl: document.getElementById("status"),
            
            // Phần tử cho video đơn
            videoInfoContainer: document.getElementById("video-info-container"),
            thumbnail: document.getElementById("thumbnail"),
            videoTitle: document.getElementById("video-title"),
            downloadMp4Btn: document.getElementById("download-mp4-btn"),
            mainVideoFormatText: document.getElementById("main-video-format-text"),
            downloadMp3Btn: document.getElementById("download-mp3-btn"),
            downloadThumbnailBtn: document.getElementById("download-thumbnail-btn"),
            formatsDropdownToggle: document.getElementById("formats-dropdown-toggle"),
            formatsDropdownList: document.getElementById("formats-dropdown-list"),

            progressContainer: document.getElementById("progress-container"),
            progressBar: document.getElementById("progress-bar"),
            progressText: document.getElementById("progress-text"),
            
            // Phần tử MỚI cho gallery Instagram
            galleryContainer: document.getElementById("gallery-container"),
            galleryTitle: document.getElementById("gallery-title"),
            galleryUploader: document.getElementById("gallery-uploader"),
            galleryGrid: document.getElementById("gallery-grid"),

            supportedGrid: document.getElementById("supported-grid"),
        };
    },

    /**
     * Gắn các sự kiện
     */
    bindEvents() {
        this.dom.form?.addEventListener("submit", this.handleGetInfo.bind(this));
        
        // Nút tải chính
        this.dom.downloadMp4Btn?.addEventListener("click", (e) => {
            e.stopPropagation();
            const formatId = e.currentTarget.dataset.formatId;
            if (formatId) this.enqueueDownload(formatId);
        });

        // Danh sách các chất lượng trong dropdown
        this.dom.formatsDropdownList?.addEventListener("click", (e) => {
            e.preventDefault();
            const target = e.target.closest("a[data-format-id]");
            if (target) {
                const formatId = target.dataset.formatId;
                this.enqueueDownload(formatId);
                this.dom.formatsDropdownList.classList.add("hidden"); // Ẩn menu sau khi chọn
            }
        });
        
        this.dom.downloadMp3Btn?.addEventListener("click", this.handleAudioDownload.bind(this));
        this.dom.downloadThumbnailBtn?.addEventListener("click", this.handleThumbnailDownload.bind(this));
        this.dom.galleryGrid?.addEventListener("click", this.handleGalleryItemDownload.bind(this));

        // Mở/đóng dropdown
        this.dom.formatsDropdownToggle?.addEventListener("click", (e) => {
            e.stopPropagation();
            this.dom.formatsDropdownList.classList.toggle("hidden");
        });
        // Tự động đóng dropdown khi click ra ngoài
        document.addEventListener('click', () => {
            if (this.dom.formatsDropdownList) {
                this.dom.formatsDropdownList.classList.add('hidden');
            }
        });

        const pasteIconContainer = document.getElementById('paste-icon-container');
        const urlInput = this.dom.urlInput; // Giả sử bạn đã cache urlInput trong this.dom

        pasteIconContainer?.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    urlInput.value = text;
                    // Tùy chọn: Tự động submit form sau khi dán
                    // this.dom.form.requestSubmit(); 
                }
            } catch (err) {
                console.error('Failed to read clipboard contents: ', err);
                alert('Không thể đọc clipboard. Vui lòng kiểm tra quyền truy cập.');
            }
        });
    },

    // --- Các hàm xử lý sự kiện ---
    handleGetInfo(event) {
        event.preventDefault();
        const url = this.dom.urlInput.value.trim();
        if (!url) { this.setStatus("Vui lòng nhập đường dẫn.", "error"); return; }
        this.state.currentURL = url;
        this.enqueueInfo(url);
    },
    
    handleAudioDownload() { 
        if (this.state.currentURL) this.enqueueAudio(); 
    },

    async handleThumbnailDownload() {
        const originalThumbUrl = this.state.currentResultData?.thumbnail;
        if (!originalThumbUrl) return;
        this.setStatus("Đang chuẩn bị hình ảnh...", "loading");
        try {
            const proxyUrl = `/api/image_proxy?url=${encodeURIComponent(originalThumbUrl)}`;
            const response = await fetch(proxyUrl);
            if (!response.ok) throw new Error('Không thể tải dữ liệu hình ảnh.');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            const filename = `${this.slugify(this.dom.videoTitle.textContent || 'thumbnail')}_witub.jpg`;
            a.style.display = 'none'; a.href = url; a.download = filename;
            document.body.appendChild(a); a.click();
            window.URL.revokeObjectURL(url); a.remove();
            this.setStatus("Tải hình ảnh thành công!", "success");
        } catch (error) { this.handleError(`Lỗi tải hình ảnh: ${error.message}`); }
    },

    handleGalleryItemDownload(event) {
        const button = event.target.closest('.gallery-download-btn');
        if (!button) return;
        
        const mediaUrl = button.dataset.url;
        const filename = button.dataset.filename;
        if (mediaUrl && filename) {
            this.enqueueGalleryItemDownload(mediaUrl, filename);
        }
    },

 
    renderSupportedPlatforms() {
        if (!this.dom.supportedGrid) return;
        this.dom.supportedGrid.innerHTML = this.config.supportedPlatforms.map(p => `
            <div class="flex items-center justify-center gap-2 bg-gray-50 p-3 rounded-lg border transition hover:shadow-md hover:border-blue-300">
                <i class="${p.icon} text-xl"></i>
                <span class="font-medium text-gray-600 text-sm">${p.name}</span>
            </div>
        `).join('');
    },
    
    /**
     * Hiển thị thông báo trạng thái
     */
    setStatus(message, type = "info") {
        const typeClasses = { info: "text-gray-600", loading: "text-blue-600", success: "text-green-600", error: "text-red-600" };
        const loadingAnimation = type === 'loading' ? '<span class="loading-dots"></span>' : '';
        this.dom.statusEl.innerHTML = message ? `<p class="${typeClasses[type]} font-medium">${message}${loadingAnimation}</p>` : "";
    },
    
   /**
     * Reset giao diện về trạng thái ban đầu
     */
   resetUI() {
    this.dom.videoInfoContainer?.classList.add('hidden');
    this.dom.galleryContainer?.classList.add('hidden');
    this.dom.submitButton.disabled = false;
    // Reset progress bar
    this.dom.progressContainer?.classList.add('hidden');
    if (this.dom.progressBar) this.dom.progressBar.style.width = '0%';
    if (this.dom.progressText) this.dom.progressText.textContent = '';
    this.setStatus('');
},

/**
 * Tạo job phân tích video/gallery
 */
async enqueueInfo(url) {
    if (!url) { this.setStatus("Vui lòng nhập đường dẫn.", "error"); return; }
    this.resetUI();
    this.setStatus("Đang phân tích", "loading");
    this.dom.submitButton.disabled = true;
    try {
        const res = await fetch("/api/enqueue/info", {
            method: "POST", headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url }),
        });
        if (!res.ok) throw new Error("Yêu cầu thất bại, vui lòng kiểm tra lại link.");
        const job = await res.json();
        this.pollJob(job.meta_key, 'info');
    } catch (error) {
        this.setStatus(`Lỗi: ${error.message}`, "error");
        this.dom.submitButton.disabled = false;
    }
},

/**
 * Tạo job tải một item từ gallery
 */
async enqueueGalleryItemDownload(mediaUrl, filename) {
    if (!mediaUrl || !filename) return;
    this.setStatus("Đang chuẩn bị tải file...", "loading");
    try {
        const res = await fetch("/api/enqueue/gallery_item", {
            method: "POST", headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ media_url: mediaUrl, filename: filename }),
        });
        if (!res.ok) throw new Error("Không thể tạo yêu cầu tải file.");
        const job = await res.json();
        this.pollJob(job.meta_key, 'download');
    } catch (error) {
        this.setStatus(`Lỗi: ${error.message}`, "error");
    }
},

/**
 * Tạo job tải video (yt-dlp)
 */
async enqueueDownload(formatId) {
    const url = this.dom.urlInput.value.trim();
    if (!url) { this.setStatus("URL video không hợp lệ.", "error"); return; }
    this.setStatus("Đang chuẩn bị tải", "loading");
    try {
        const res = await fetch("/api/enqueue/download", {
            method: "POST", headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url, format_id: formatId }),
        });
        if (!res.ok) throw new Error("Không thể tạo yêu cầu tải.");
        const job = await res.json();
        this.pollJob(job.meta_key, 'download');
    } catch (error) {
        this.setStatus(`Lỗi: ${error.message}`, "error");
    }
},

/**
 * Tạo job tải audio (yt-dlp)
 */
async enqueueAudio() {
    const url = this.dom.urlInput.value.trim();
    if (!url) { this.setStatus("URL video không hợp lệ.", "error"); return; }
    this.setStatus("Đang chuẩn bị tải", "loading");
    try {
        const res = await fetch("/api/enqueue/audio", {
            method: "POST", headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url }),
        });
        if (!res.ok) throw new Error("Không thể tạo yêu cầu tải.");
        const job = await res.json();
        this.pollJob(job.meta_key, 'download');
    } catch (error) {
        this.setStatus(`Lỗi: ${error.message}`, "error");
    }
},

/**
 * Theo dõi trạng thái job
 */
pollJob(metaKey, jobType) {
    if (this.state.isPolling) return;
    this.state.isPolling = true;
    const intervalId = setInterval(async () => {
        try {
            const res = await fetch(`/api/job/${metaKey}`);
            if (!res.ok) throw new Error("Job không tồn tại.");
            const meta = await res.json();
            
            if (jobType === 'download' && (meta.status === 'running' || meta.status === 'queued')) {
                this.dom.progressContainer.classList.remove('hidden');
                const progress = meta.progress || 0;
                this.dom.progressBar.style.width = `${progress}%`;
                this.dom.progressText.textContent = `Đang tải... ${progress}%`;
            } else if (jobType === 'info') {
                this.setStatus("Đang phân tích", 'loading');
            }

            if (meta.status === "finished" || meta.status === "error") {
                clearInterval(intervalId);
                this.state.isPolling = false;
                this.dom.submitButton.disabled = false;
                
                if (meta.status === "finished") {
                    if (jobType === 'info') {
                        this.setStatus("Kết quả của bạn đây! 🎉", "success");
                        if (meta.result.type === 'gallery') {
                            this.renderGallery(meta.result);
                        } else {
                            this.renderVideoInfo(meta.result);
                        }
                    } else {
                        this.setStatus("Đang tải file về máy", "loading");
                        this.downloadFile(metaKey);
                    }
                } else {
                     this.setStatus(`Lỗi: ${meta.error || 'Không xác định'}`, "error");
                }
            }
        } catch (error) {
            this.setStatus(error.message, "error");
            clearInterval(intervalId);
            this.state.isPolling = false;
            this.dom.submitButton.disabled = false;
        }
    }, this.config.pollInterval);
},

/**
 * Tải file kết quả
 */
async downloadFile(metaKey) {
    try {
        const res = await fetch(`/api/job/${metaKey}/result`);
        if (!res.ok) throw new Error("Không tìm thấy file kết quả.");
        const blob = await res.blob();
        const header = res.headers.get("content-disposition");
        const filename = header ? header.split('filename=')[1].replace(/"/g, '') : "download";
        const link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(link.href);
        this.setStatus("Tải về hoàn tất!", "success");
    } catch (error) {
        this.setStatus(`Lỗi tải file: ${error.message}`, "error");
    }
},

/**
 * Hiển thị giao diện gallery
 */
renderGallery(info) {
    if (!info || !info.items) return;

    this.dom.videoInfoContainer?.classList.add("hidden");
    this.dom.galleryContainer?.classList.remove("hidden");

    this.dom.galleryTitle.textContent = info.title || "Instagram Post";
    this.dom.galleryUploader.textContent = `@${info.uploader}`;
    this.dom.galleryGrid.innerHTML = "";

    info.items.forEach(item => {
        const itemEl = document.createElement("div");
        itemEl.className = "relative group aspect-square bg-gray-100 rounded-lg overflow-hidden shadow";
        
        // NÂNG CẤP:
        // 1. Dùng proxy để hiển thị `thumbnail_url`
        const thumbnailUrl = `/api/image_proxy?url=${encodeURIComponent(item.thumbnail_url)}`;
        
        itemEl.innerHTML = `
            <img src="${thumbnailUrl}" class="w-full h-full object-cover transition-transform group-hover:scale-105">
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center">
                <button 
                    class="gallery-download-btn opacity-0 group-hover:opacity-100 transition-opacity bg-white text-gray-800 rounded-full w-12 h-12 flex items-center justify-center shadow-lg transform hover:scale-110"
                    
                    data-url="${item.media_url}" 
                    data-filename="${item.filename}"
                    title="Tải xuống file này"
                >
                    <i class="fa-solid fa-download text-xl"></i>
                </button>
            </div>
            ${item.is_video ? '<i class="fa-solid fa-play absolute top-2 right-2 text-white text-xs bg-black bg-opacity-40 rounded-full w-5 h-5 flex items-center justify-center"></i>' : ''}
        `;
        this.dom.galleryGrid.appendChild(itemEl);
    });
},

/**
 * Hiển thị thông tin video đơn
 */
renderVideoInfo(info) {
    if (!info) { this.setStatus("Không nhận được dữ liệu hợp lệ.", "error"); return; }
    
    this.dom.galleryContainer?.classList.add("hidden");
    this.dom.videoInfoContainer?.classList.remove("hidden");

    this.dom.videoTitle.textContent = info.title || "Không có tiêu đề";
    const thumbnailUrl = info.thumbnail ? `/api/image_proxy?url=${encodeURIComponent(info.thumbnail)}` : '';
    this.dom.thumbnail.src = thumbnailUrl;
    
    // LOGIC QUAN TRỌNG: Ẩn/Hiện nút tải video
    const downloadContainer = document.getElementById('video-download-container');
    if (info.best_mp4_format_id) {
        downloadContainer.style.display = 'inline-flex'; // Hiện nút
        this.dom.downloadMp4Btn.dataset.formatId = info.best_mp4_format_id;
        this.dom.mainVideoFormatText.textContent = info.best_mp4_format_text || "MP4";
        this.dom.downloadMp4Btn.disabled = false;
    } else {
        downloadContainer.style.display = 'none'; // Ẩn nút nếu không có định dạng nào
    }

    let dropdownHTML = '';
    const withAudio = info.formats_with_audio || [];
    const withoutAudio = info.formats_without_audio || [];

    const createItemHTML = (f, hasAudio) => `
        <li><a href="#" class="flex justify-between items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-format-id="${f.format_id}">
            <span class="font-medium">${f.resolution}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">${f.filesize_str || ''}</span>
                <i class="fas ${hasAudio ? 'fa-volume-up text-gray-500' : 'fa-volume-mute text-gray-400'}"></i>
            </div>
        </a></li>`;

    if (withAudio.length > 0) {
        dropdownHTML += '<li class="px-4 py-2 text-xs text-gray-500 font-semibold">Video + Âm thanh</li>';
        withAudio.forEach(f => dropdownHTML += createItemHTML(f, true));
    }
    if (withoutAudio.length > 0) {
        dropdownHTML += '<li class="px-4 py-2 text-xs text-gray-500 font-semibold border-t mt-1">Video (Không có âm thanh)</li>';
        withoutAudio.forEach(f => dropdownHTML += createItemHTML(f, false));
    }
    
    this.dom.formatsDropdownList.innerHTML = dropdownHTML || `<li class="px-4 py-2 text-sm text-gray-500">Không có định dạng phù hợp</li>`;
},

};

// Khởi chạy ứng dụng
document.addEventListener("DOMContentLoaded", () => VideoDownloaderApp.init());

// Thêm CSS cho hiệu ứng loading dots
const style = document.createElement('style');
style.innerHTML = `
.loading-dots::after {
  content: '.';
  animation: dots 1s steps(5, end) infinite;
}
@keyframes dots {
  0%, 20% { color: rgba(0,0,0,0); text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); }
  40% { color: inherit; text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); }
  60% { text-shadow: .25em 0 0, .5em 0 0 rgba(0,0,0,0); }
  80%, 100% { text-shadow: .25em 0 0, .5em 0 0; }
}`;
document.head.appendChild(style);

