{if $__wcf->session->getPermission('mod.board.canViewPostVersions') && $hasVersions|isset && $hasVersions[$post->postID]|isset}
	<li class="">
		<a href="{link application='wbb' controller='PostVersions' id=$post->postID}{/link}" title="{lang}wbb.post.edithistory.title{/lang}" class="button jsTooltip">
			<span class="icon icon16 icon-undo"></span> 
			<span class="invisible">{lang}wbb.post.edithistory.title{/lang}</span>
		</a>
	</li>
{/if}