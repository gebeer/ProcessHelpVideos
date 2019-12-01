
var ProcessHelpVideos = {
    'init': function () {
        ProcessHelpVideos.initVideoJs();
    },
    'initVideoJs': function () {
        var videos = document.querySelectorAll('.video-js');
        videos.forEach(function (video) {
            var videoId = video.getAttribute("id");
            var options = JSON.parse(video.getAttribute('data-vjsoptions'));
            videojs(video, options);
        });
    }
};

document.addEventListener('DOMContentLoaded', ProcessHelpVideos.init);
