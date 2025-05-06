export default class Comments {
    constructor() {
        this.init();
    }

    init() {
        this.setupDisplayReply();
        this.setupAbandonment();
        this.setupCommentButton();
        this.setupReplyButtons();
    }

    // Show collapse on click event
    setupDisplayReply() {
        const displayReplyButtons = document.querySelectorAll("#displayReply");
        if (displayReplyButtons.length > 0) {
            displayReplyButtons.forEach(element => {
                element.addEventListener('click', function (event) {
                    const collapseComment = document.querySelector("#collapseComment");
                    const textAreaComment = document.getElementById('comments_form_content');
    
                    if (collapseComment) {
                        collapseComment.classList.add('show');
    
                        if (textAreaComment) {
                            setTimeout(() => {
                                textAreaComment.scrollIntoView({ behavior: "smooth", block: "center" });
                                textAreaComment.focus();
                            }, 300);
                        }
                    }
                });
            });
        }
    }
    

    // Hide collapse on click event
    setupAbandonment() {
        const abandonment = document.getElementById('abandonment');
        if (abandonment) {
            abandonment.addEventListener('click', () => {
                const div = document.getElementById('collapseComment');
                const textAreaComment = document.getElementById('comments_form_content');
                if (div) {
                    div.classList.remove('show');
                }

                if (textAreaComment) {
                    textAreaComment.value = '';
                }
            });
        }
    }

    // Disable submit button for comment on keyup event
    setupCommentButton() {
        const textAreaComment = document.getElementById('comments_form_content');
        if (textAreaComment) {
            const commentButton = document.getElementById('submit-comment');
            textAreaComment.addEventListener('keyup', function () {
                const textareaContent = this.value.trim();
                if (textareaContent !== '') {
                    commentButton.removeAttribute('disabled');
                } else {
                    commentButton.setAttribute('disabled', 'true');
                }
            });
        }
    }

    // On met un écouteur d'évènements sur tous les boutons répondre
    setupReplyButtons() {
        const comments = document.getElementById('comments');
        if (comments) {
            document.querySelectorAll("[data-reply]").forEach(element => {
                element.addEventListener('click', function () {
                    document.querySelector("#comments_form_parent").value = this.dataset.id;
                });
            });
        }
    }
}