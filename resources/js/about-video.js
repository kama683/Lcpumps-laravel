// Port of components/ui/AboutVideo.tsx's click-to-play poster->video swap.
export function initAboutVideo() {
    document.querySelectorAll('[data-about-video]').forEach((button) => {
        button.addEventListener('click', () => {
            const video = document.createElement('video');
            video.src = button.dataset.videoSrc;
            video.autoplay = true;
            video.controls = true;
            video.loop = true;
            video.playsInline = true;
            video.className = `absolute inset-0 size-full object-cover ${button.dataset.extraClass || ''}`.trim();
            button.replaceWith(video);
        });
    });
}
