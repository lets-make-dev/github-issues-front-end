<div x-data="addCommentModelData()">
<!-- Add Comment Modal -->
<div x-show="showModalAddCommentModel" x-cloak class="fixed top-0 right-0 bottom-0 left-0 inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Comments</h2>
            <button @click="showModalAddCommentModel = false" class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;</button>
        </div>
        <!-- Comment Submission Form -->
        <form wire:submit.prevent="addComment()" class="mt-3">
            <label for="newComment" class="block text-gray-700 dark:text-gray-300">Add a New Comment:</label>
            <textarea wire:model.defer="newComment" id="newComment" rows="3"
                class="w-full p-2 border rounded-md dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white mt-2"></textarea>
            @error('newComment')
                <span class="text-red-500 text-sm mt-1 block w-full">{{ $message }}</span>
            @enderror

            <button type="submit"
                class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-200">
                Submit Comment
            </button>
            <button @click="showModalAddCommentModel = false" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Close
            </button>
        </form>
    </div>
</div>
<script>
    function addCommentModelData() {
        return {
            showModalAddCommentModel : false,
            init(){
                window.addEventListener('open-add-comment-modal', event => {
                    this.showModalAddCommentModel = true ;

                });
                 window.addEventListener('commentAddedSucessfully', event => {
                    this.showModalAddCommentModel = false ;
                     const messageElement = document.getElementById('successMessage');
                     messageElement.style.display = 'block';
                     messageElement.innerHTML  = event.detail;
                     setTimeout(() => {
                         messageElement.style.display = 'none';
                     }, 3000);
                 });
            }
        }
    }
 </script>
</div>
