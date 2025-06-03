let currentIndex = 0;
const images = document.getElementById('carousel-images');
const totalImages = images.children.length;

function updateCarousel() {
    images.style.transform = `translateX(-${currentIndex * 100}%)`;
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % totalImages;
    updateCarousel();
}

function prevSlide() {
    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    updateCarousel();
}

function openModal(imgElement) {
    // 获取图片的src属性
    const imageUrl = imgElement.src;
    
    // 跳转到新页面，并将图片的URL作为查询参数传递
    window.location.href = `img.html?image=${encodeURIComponent(imageUrl)}`;
}


