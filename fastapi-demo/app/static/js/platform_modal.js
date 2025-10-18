document.addEventListener('DOMContentLoaded', () => {
    const showButton = document.getElementById('show-platforms-modal-btn');
    const modal = document.getElementById('platforms-modal');
    const closeButton = document.getElementById('close-platforms-modal-btn');
    const modalContent = document.getElementById('platforms-modal-content');

    // --- DỮ LIỆU NỀN TẢNG ĐÃ ĐƯỢC MỞ RỘNG VÀ PHÂN LOẠI ---
    const platformCategories = [
        {
            title: "Mạng Xã Hội & Video Ngắn",
            platforms: [
                { name: 'YouTube', icon: 'fab fa-youtube', color: 'text-red-500' },
                { name: 'Facebook', icon: 'fab fa-facebook', color: 'text-blue-600' },
                { name: 'TikTok', icon: 'fab fa-tiktok', color: 'text-black' },
                { name: 'Instagram', icon: 'fab fa-instagram', color: 'text-pink-500' },
                { name: 'Threads', icon: 'fab fa-threads', color: 'text-black' },
                { name: 'X (Twitter)', icon: 'fab fa-twitter', color: 'text-blue-400' },
                { name: 'Reddit', icon: 'fab fa-reddit-alien', color: 'text-orange-600' },
                { name: 'Pinterest', icon: 'fab fa-pinterest', color: 'text-red-700' },
                { name: 'Tumblr', icon: 'fab fa-tumblr', color: 'text-blue-900' },
                { name: 'Likee', icon: 'fas fa-heart', color: 'text-red-500' },
                { name: 'Kwai', icon: 'fas fa-video', color: 'text-orange-500' },
            ]
        },
        {
            title: "Video Chuyên Dụng & Sáng Tạo",
            platforms: [
                { name: 'Vimeo', icon: 'fab fa-vimeo-v', color: 'text-cyan-500' },
                { name: 'Dailymotion', icon: 'fas fa-play-circle', color: 'text-gray-800' },
                { name: 'Behance', icon: 'fab fa-behance-square', color: 'text-blue-700' },
                { name: 'ArtStation', icon: 'fab fa-artstation', color: 'text-sky-500' },
                { name: 'Flickr', icon: 'fab fa-flickr', color: 'text-pink-600' },
                { name: '500px', icon: 'fab fa-500px', color: 'text-black' },
            ]
        },
        {
            title: "Âm Nhạc & Podcast",
            platforms: [
                { name: 'SoundCloud', icon: 'fab fa-soundcloud', color: 'text-orange-500' },
                { name: 'Bandcamp', icon: 'fab fa-bandcamp', color: 'text-teal-500' },
                { name: 'Mixcloud', icon: 'fab fa-mixcloud', color: 'text-blue-800' },
                { name: 'Audiomack', icon: 'fas fa-headphones', color: 'text-yellow-500' },
            ]
        },
        {
            title: "Gaming & Streaming",
            platforms: [
                { name: 'Twitch', icon: 'fab fa-twitch', color: 'text-purple-600' },
                { name: 'Steam', icon: 'fab fa-steam', color: 'text-gray-800' },
                { name: 'NicoNico', icon: 'fas fa-comment', color: 'text-gray-600' },
            ]
        },
        {
            title: "Tin Tức & Giáo Dục",
            platforms: [
                { name: 'BBC', icon: 'fas fa-newspaper', color: 'text-black' },
                { name: 'CNN', icon: 'fas fa-tv', color: 'text-red-600' },
                { name: 'TED', icon: 'fas fa-microphone-alt', color: 'text-red-600' },
                { name: 'Khan Academy', icon: 'fas fa-graduation-cap', color: 'text-green-700' },
            ]
        },
         {
            title: "Nền Tảng Khu Vực",
            platforms: [
                { name: 'Bilibili', icon: 'fas fa-tv', color: 'text-sky-500' },
                { name: 'Douyin', icon: 'fab fa-tiktok', color: 'text-black' },
                { name: 'VK', icon: 'fab fa-vk', color: 'text-sky-600' },
                { name: 'Youku', icon: 'fas fa-play', color: 'text-blue-500' },
                { name: 'iQIYI', icon: 'fas fa-play', color: 'text-green-500' },
            ]
        }
    ];

    // --- Hàm để "vẽ" các nền tảng vào popup ---
    function renderPlatforms() {
        if (!modalContent) return;
        modalContent.innerHTML = ''; // Xóa nội dung cũ
        
        platformCategories.forEach(category => {
            // Tạo tiêu đề cho mỗi danh mục
            const categoryTitle = document.createElement('h4');
            categoryTitle.className = 'text-md font-bold text-gray-800 mb-3';
            categoryTitle.textContent = category.title;
            
            // Tạo lưới cho các nền tảng trong danh mục
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4';
            
            grid.innerHTML = category.platforms.map(p => `
                <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <i class="${p.icon} ${p.color} text-2xl w-6 text-center"></i>
                    <span class="font-semibold text-gray-700">${p.name}</span>
                </div>
            `).join('');
            
            modalContent.appendChild(categoryTitle);
            modalContent.appendChild(grid);
        });
    }

    // --- Các hàm và sự kiện để điều khiển popup (giữ nguyên) ---
    function openModal() { modal?.classList.remove('hidden'); }
    function closeModal() { modal?.classList.add('hidden'); }
    showButton?.addEventListener('click', openModal);
    closeButton?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    // Chạy hàm "vẽ" ngay từ đầu
    renderPlatforms();
});