export default class LikeController {
    constructor() {
        this.init();
    }

    init() {
        this.clickLikeComment();
    }

    clickLikeComment() {
        const likeButtons = document.querySelectorAll('.like-button');
        likeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const commentId = button.getAttribute('data-comment-id')
                this.likeDislikeComment(commentId)
            });
        });
    }

    likeDislikeComment(commentId) {
        fetch(`/like-dislike-comment/${commentId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                this.updateLikeCount(commentId, data.likeCount);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    updateLikeCount(commentId, likeCount) {
        const likeCountElement = document.querySelector(`.like-count[data-comment-id="${commentId}"]`);
        if (likeCountElement) {
            likeCountElement.textContent = likeCount;
        }
    }
}