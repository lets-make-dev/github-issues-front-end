@props(['issue', 'repoName'])
<div x-data="issueData">
<div class="border-2 dark:border border-gray-900 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">
    <div class="flex justify-between items-start">

        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $issue['title'] }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $repoName }}</p>
        </div>
        <div class="flex items-center space-x-2">


            <span class="{{ $issue['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-300 text-gray-800' }} text-xs font-semibold px-2 py-1 rounded-full border">
                {{ $issue['status'] }}
            </span>
            @foreach($issue['priorities'] as $priority)
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                    {{ $priority }}
                </span>
            @endforeach
        </div>
    </div>
    <p class="text-gray-700 dark:text-gray-200 mt-2">{!! $issue['description'] !!}</p>
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            @foreach($issue['labels'] as $color => $label)
                <x-dashboard.issues.label :label="$label" :color="$color"/>
            @endforeach
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-4">
            <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                    </svg>
                    {{-- <span @click="showModalCommentsListModel = true;" >{{ $issue['comments'] }} comments</span> --}}
                    <span @click="showModalCommentsListModel = true;" >{{ $issue['comments_count'] }} comments</span>
                </div>
                <button @click="showModalAddCommentModel = true" class="rounded">
                    Add a Comment
                </button>

            </div>
        </div>
    </div>

    <div class="mt-2 flex items-center justify-between">
        @if($issue['milestone'])
            <span class="text-sm text-gray-600 bg-gray-200 px-2 py-1 rounded">🏁 Milestone: {{ $issue['milestone'] }}</span>
        @else
            <span></span>
        @endif

        @if(isset($issue['estimated_hours']) && $issue['estimated_hours'] > 0)
            <x-progress-bar :issue="$issue" class="flex-grow ml-4"/>
        @endif
    </div>
</div>

<!-- Add Comment Modal -->
<div x-show="showModalAddCommentModel" x-cloak class="fixed top-0 right-0 bottom-0 left-0 inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Comments</h2>
            <button @click="showModalAddCommentModel = false" class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;</button>
        </div>
        <!-- Comment Submission Form -->
        <form wire:submit.prevent="addComment({{ $issue['issue_number'] }})" class="mt-3">
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

 <!--  Show Comments of Issue Modal-->
 <div x-show="showModalCommentsListModel" x-cloak class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
    <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-3xl relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Comments</h2>
            <button @click="showModalCommentsListModel = false" class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;</button>
        </div>
        <ul class="flex flex-col space-y-4 mt-4">
            @forelse ($issue['issues_comments'] as $comment)
            <li>
                <div class="flex items-center space-x-3">
                    <!-- <div class="flex flex-shrink-0 self-start cursor-pointer">
                        <img src="https://pixabay.com/get/g5745029bc213d7b651f8b650058c0a892eeb1eed2fe6d278870cd36077733a61b8200868ea657c55d732d4489cf1f83a166ae44bf57a2d04f45faea2e2462336c1fdd88416ac17372ac9c0f50b188995_640.png" alt="" class="h-8 w-8 object-fill rounded-full">
                    </div> -->
                    <div class="block w-full">
                        <div class="bg-white border border-gray-200 w-auto rounded-lg overflow-hidden">
                            <!-- <div class="font-semibold mb-1">
                                <a href="#" class="hover:underline text-sm text-indigo-600">Nirmala</a>
                            </div> -->
                            <div class="bg-gray-100 border-b border-gray-200 text-sm py-2 px-3">
                                commented 23 minutes ago
                            </div>
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
            showModalAddCommentModel: false,
            showModalCommentsListModel: false,
            init(){
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



<!-- Static -->
{{--<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">--}}
{{--    <div class="flex justify-between items-start">--}}
{{--        <div>--}}
{{--            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Implement user authentication</h3>--}}
{{--            <p class="text-sm text-gray-600 dark:text-gray-300">Admin Dashboard</p>--}}
{{--        </div>--}}
{{--        <div class="flex items-center space-x-2">--}}
{{--            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full border">--}}
{{--                {{ $issue['status'] }}--}}
{{--            </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    High--}}
{{--                </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    Security--}}
{{--                </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    User Communication--}}
{{--                </span>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <p class="text-gray-700 dark:text-gray-200 mt-2">We need to add user authentication to the application.</p>--}}
{{--    <div class="mt-4 flex items-center justify-between">--}}
{{--        <div class="flex items-center space-x-2">--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-blue-100 text-blue-800 border-blue-800">--}}
{{--                Feature--}}
{{--            </span>                            --}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-yellow-100 text-yellow-800 border-yellow-800">--}}
{{--                Security--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-red-100 text-red-800 border-red-800">--}}
{{--                Bug--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-purple-100 text-purple-800 border-purple-800">--}}
{{--                UI--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-pink-100 text-pink-800 border-pink-800">--}}
{{--                Backend--}}
{{--            </span>--}}
{{--        </div>--}}
{{--        <div class="text-sm text-gray-600 dark:text-gray-300">--}}
{{--            <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>--}}
{{--            <span class="ml-4">💬 {{ $issue['comments'] }} comments</span>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="mt-2 flex items-center justify-between">--}}
{{--        <span class="text-sm bg-gray-200 text-gray-800 px-2 py-1 rounded">🏁 Milestone: v1.0 Release</span>--}}
{{--        <div class="@ flex justify-end items-center text-sm text-gray-600 dark:text-gray-300">--}}
{{--            <span class="mr-4">Est. 2023-06-30</span>--}}
{{--            <span class="mr-2">5 / 20 hrs</span>--}}
{{--            <div class="w-24">--}}
{{--                <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">--}}
{{--                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 25%"></div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
