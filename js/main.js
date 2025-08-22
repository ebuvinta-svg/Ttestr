document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('upload-form');
    const uploadStatus = document.getElementById('upload-status');
    const galleryContainer = document.getElementById('gallery-container');

    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Show loading status
            submitButton.disabled = true;
            submitButton.innerHTML = 'Uploading...';
            uploadStatus.innerHTML = '<p>Uploading, please wait...</p>';
            uploadStatus.style.color = 'blue';

            fetch('upload.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    uploadStatus.innerHTML = `<p>${data.message}</p>`;
                    uploadStatus.style.color = 'green';

                    // Reset form
                    uploadForm.reset();

                    // Add new photo to the gallery
                    const newPhoto = data.photo;
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    galleryItem.innerHTML = `
                        <a href="photo.php?id=${newPhoto.id}">
                            <img class="lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="uploads/${newPhoto.filename}" alt="${newPhoto.title}">
                            <h3>${newPhoto.title}</h3>
                        </a>
                    `;

                    // Remove the "no photos" message if it exists
                    const noPhotosMessage = galleryContainer.querySelector('.no-photos-message');
                    if (noPhotosMessage) {
                        noPhotosMessage.remove();
                    }

                    galleryContainer.prepend(galleryItem);
                    // Observe the new image for lazy loading
                    lazyLoadObserver.observe(galleryItem.querySelector('.lazy'));

                } else {
                    // Show error message
                    uploadStatus.innerHTML = `<p>${data.message}</p>`;
                    uploadStatus.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                uploadStatus.innerHTML = '<p>An unexpected error occurred. Please try again.</p>';
                uploadStatus.style.color = 'red';
            })
            .finally(() => {
                // Restore button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // --- Lazy Loading for Images ---
    const lazyImages = document.querySelectorAll('img.lazy');

    const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                img.addEventListener('load', () => {
                    // Optional: add a class to fade in the loaded image
                    img.classList.add('loaded');
                });
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => {
        lazyLoadObserver.observe(img);
    });
});
