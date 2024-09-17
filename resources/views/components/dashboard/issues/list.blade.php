@props(['issues', 'repoName'])

<div class="space-y-4">
    @foreach($issues as $issue)
        <x-dashboard.issues.item :issue="$issue" :repo-name="$repoName" />
    @endforeach
</div>
