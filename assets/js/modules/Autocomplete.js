export default class Autocomplete {
    constructor() {
        this.init();
    }

    init() {
        const autocompleteField = document.querySelector('#course_autocomplete');

        if (autocompleteField) {
            autocompleteField.addEventListener('change', async (event) => {
                const courseId = event.target.value;

                if (courseId) {
                    try {
                        const response = await fetch(`/course/details/${courseId}`);
                        const data = await response.json();

                        if (data.program_slug && data.section_slug && data.slug) {
                            window.location.href = `/courses/${data.program_slug}/${data.section_slug}/${data.slug}`;
                        }
                    } catch (error) {
                        console.error('Error fetching course details:', error);
                    }
                }
            });
        }
    }
}