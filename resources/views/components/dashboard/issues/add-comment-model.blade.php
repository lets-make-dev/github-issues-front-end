<div x-data="addCommentModelData()">
<!-- Add Comment Modal -->
<div x-show="showModalAddCommentModel" x-cloak class="fixed top-0 right-0 bottom-0 left-0 inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Comments</h2>
            <button @click="closePopUp()" class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;</button>
        </div>
        <!-- Comment Submission Form -->
        <form wire:submit.prevent="addComment()" class="mt-3">
            <label for="newComment" class="block text-gray-700 dark:text-gray-300">Add a New Comment:</label>
            <div wire:ignore >
                <textarea wire:model.defer="newComment" id="newComment" rows="3"
                    class="w-full p-2 border rounded-md dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white mt-2"></textarea>
            </div>
            @error('newComment')
                <span class="text-red-500 text-sm mt-1 block w-full">{{ $message }}</span>
            @enderror

            <button
                @click="addComment()"
                type="submit"
                class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-200">
                <span wire:loading.class.remove="hidden" class="hidden">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                </span>
                Submit Comment
            </button>
            <button type="button" @click="closePopUp()" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Close
            </button>
        </form>
    </div>
</div>
<script>
    function addCommentModelData() {
        return {

            easyMDE: null,
            showModalAddCommentModel : false,
            element: null,

            init(){
                this.element = document.getElementById('newComment') ? document.getElementById('newComment') : null;
                console.log('init calling..');

                window.addEventListener('open-add-comment-modal', event => {
                    this.showModalAddCommentModel = true ;
                    this.initEasyMDE();

                });
                 window.addEventListener('commentAddedSucessfully', event => {
                    this.showModalAddCommentModel = false ;
                    const messageElement = document.getElementById('successMessage');
                    messageElement.style.display = 'block';
                    messageElement.innerHTML  = event.detail;
                    this.closePopUp();
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 2000);
                 });
            },

            initEasyMDE() {
                setTimeout(() => {
                    this.easyMDE = new EasyMDE({
                        maxHeight: '40vh',
                        element: this.element,
                        uploadImage: true,
                        imageUploadFunction: this.uploadImage.bind(this),
                        imageMaxSize: 2 * 1024 * 1024,
                        imageAccept: 'image/*',
                    });
                }, 200);
            },


            addComment()
            {
                console.log(this.easyMDE.value());

                if(this.easyMDE.value() == '')
                {
                    return;
                }
                console.log('set value');
                this.$wire.setComment(this.easyMDE.value());
            },

            closePopUp() {
                    if (this.easyMDE) {
                        this.easyMDE.toTextArea(); // Convert back to textarea
                        this.easyMDE = null;       // Destroy the instance
                    }
                    if (this.element) {
                        this.element.value = '';   // Clear the textarea content
                    }
                    this.showModalAddCommentModel = false;
                },

            uploadImage(file) {
                let formData = new FormData();
                formData.append('image', file);

                console.log(formData);
                axios.post('/upload-image', formData, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'multipart/form-data',
                    }
                })
                .then(response => {
                    const cm = this.easyMDE.codemirror;
                    const cursorPosition = cm.getCursor();
                    const imageMarkdown = `![${response.data.name}](${response.data.url})`;
                    cm.replaceRange(imageMarkdown, cursorPosition);
                    const newCursorPosition = {
                        line: cursorPosition.line + 1,
                        ch: 0
                    };
                    cm.setCursor(newCursorPosition);
                })
                .catch(error => {
                    console.log(error);
                });

            }
        }
    }
 </script>
</div>
