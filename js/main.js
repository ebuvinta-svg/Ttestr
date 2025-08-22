document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('upload-form');
    const galleryContainer = document.getElementById('gallery-container');
    const lightboxModal = document.getElementById('lightbox-modal');
    const lightboxContent = document.getElementById('lightbox-photo-container');
    const lightboxClose = document.querySelector('.lightbox-close');
    const toastContainer = document.getElementById('toast-container');

    // --- UI Helpers ---
    const ICONS = {
        download: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
        delete: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>`
    };

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        toast.addEventListener('animationend', () => {
            if (toast.style.animationName === 'fadeOut') {
                toast.remove();
            }
        });
    }


    // --- Uploader ---
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            submitButton.disabled = true;
            submitButton.innerHTML = 'Uploading...';

            fetch('upload.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    uploadForm.reset();
                    const newPhoto = data.photo;
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    galleryItem.innerHTML = `
                        <a href="photo.php?id=${newPhoto.id}">
                            <img class="lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="uploads/${newPhoto.filename}" alt="${newPhoto.title}">
                            <h3>${newPhoto.title}</h3>
                        </a>
                    `;
                    const noPhotosMessage = galleryContainer.querySelector('.no-photos-message');
                    if (noPhotosMessage) noPhotosMessage.remove();
                    galleryContainer.prepend(galleryItem);
                    lazyLoadObserver.observe(galleryItem.querySelector('.lazy'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An unexpected error occurred.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // --- Lazy Loading ---
    const lazyImages = document.querySelectorAll('img.lazy');
    const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                img.addEventListener('load', () => img.classList.add('loaded'));
                observer.unobserve(img);
            }
        });
    });
    lazyImages.forEach(img => lazyLoadObserver.observe(img));

    // --- Lightbox ---
    if (galleryContainer) {
        galleryContainer.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault();
                openLightbox(new URL(link.href).searchParams.get('id'));
            }
        });
    }

    function openLightbox(photoId) {
        lightboxContent.innerHTML = '<p>Loading photo...</p>';
        lightboxModal.style.display = 'block';

        fetch(`get_photo.php?id=${photoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const photo = data.photo;
                    const csrfToken = document.body.dataset.csrfToken;
                    const lang = JSON.parse(document.body.dataset.langJs);
                    lightboxContent.innerHTML = `
                        <div class="photo-container">
                            <h2>${photo.title}</h2>
                            <img src="uploads/${photo.filename}" alt="${photo.title}">
                            <p>${nl2br(photo.description)}</p>
                            <div class="photo-actions">
                                <a href="uploads/${photo.filename}" download class="icon-btn download-btn" title="${lang.download_button}">${ICONS.download}</a>
                                <form action="delete.php" method="POST" style="display: inline;" onsubmit="return confirm('${lang.delete_confirm}');">
                                    <input type="hidden" name="photo_id" value="${photo.id}">
                                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                                    <button type="submit" class="icon-btn delete-btn" title="${lang.delete_button}">${ICONS.delete}</button>
                                </form>
                            </div>
                        </div>
                    `;
                } else {
                    lightboxContent.innerHTML = `<p>${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                lightboxContent.innerHTML = '<p>Could not load photo.</p>';
            });
    }

    function nl2br (str) { return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'); }
    function closeLightbox() { lightboxModal.style.display = 'none'; lightboxContent.innerHTML = ''; }

    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    lightboxModal.addEventListener('click', e => { if (e.target === lightboxModal) closeLightbox(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && lightboxModal.style.display === 'block') closeLightbox(); });
});
