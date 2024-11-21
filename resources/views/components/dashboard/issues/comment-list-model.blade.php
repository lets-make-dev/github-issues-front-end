@props(['comments'])
<div x-data="issueData()">
    <!--  Show Comments of Issue Modal-->
    <div x-show="showModalCommentsListModel" x-cloak
         class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
        <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-3xl relative">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Comments</h2>
                <button @click="showModalCommentsListModel = false"
                        class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;
                </button>
            </div>
            <ul class="scrollbar-thin flex flex-col space-y-4 mt-4 overflow-auto max-h-[65vh] pr-5 ">
                @forelse ($comments as $comment)
                    <li>
                        <div class="flex items-center space-x-3">
                            <!-- <div class="flex flex-shrink-0 self-start cursor-pointer">
                                <img src="https://pixabay.com/get/g5745029bc213d7b651f8b650058c0a892eeb1eed2fe6d278870cd36077733a61b8200868ea657c55d732d4489cf1f83a166ae44bf57a2d04f45faea2e2462336c1fdd88416ac17372ac9c0f50b188995_640.png" alt="" class="h-8 w-8 object-fill rounded-full">
                            </div> -->
                            <div class="block w-full">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 w-auto rounded-lg overflow-hidden">
                                    <!-- <div class="font-semibold mb-1">
                                        <a href="#" class="hover:underline text-sm text-indigo-600">Nirmala</a>
                                    </div> -->

                                    @if ($comment->created_at->diffInMonths() >= 1)
                                        <div class="bg-white dark:bg-gray-900 text-sm py-2 px-3">
                                            commented on {{ $comment->created_at->format('F j, Y') }}
                                        </div>
                                    @else
                                        <div class="bg-white dark:bg-gray-900 text-sm py-2 px-3">
                                            commented {{ $comment->created_at->diffForHumans() }}
                                        </div>
                                    @endif
                                    <div class="md:text-sm p-3">{{ $comment->content }}</div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <p>No Comments</p>
                @endforelse
            </ul>
        </div>
    </div>
    <script>
        function issueData() {
            return {
                showModalCommentsListModel: false,
                init() {
                    window.addEventListener('open-modal', event => {
                        this.showModalCommentsListModel = true;
                    });
                }
            }
        }
    </script>
    <style>
        .addCommentToIssueModel {
            scrollbar-width: thin;
        }


        .addCommentToIssueModel::-webkit-scrollbar {
            width: 8px;
        }

        .addCommentToIssueModel::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }

        .addCommentToIssueModel::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
    </style>
</div>
