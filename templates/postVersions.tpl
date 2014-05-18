{include file='documentHeader'}

<head>
	<title>{lang}wbb.post.edithistory.versions{/lang} - {PAGE_TITLE|language}</title>

	{include file='headInclude'}

	{if $pageNo < $pages}
		<link rel="next" href="{link application='wbb' controller='PostVersions' id=$post->postID}pageNo={@$pageNo+1}{/link}" />
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link application='wbb' controller='PostVersions' id=$post->postID}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}" />
	{/if}

	<script data-relocate="true" src="{@$__wcf->getPath('wbb')}js/WBB.EditHistory{if !ENABLE_DEBUG_MODE}.min{/if}.js"></script>
	<script data-relocate="true">
		//<![CDATA[ 
		$(function() {
			new WCF.Message.BBCode.CodeViewer();
			new WBB.EditHistory.Revert();
			new WBB.EditHistory.IPAddressHandler();

			WCF.Language.addObject({
				'wbb.post.ipAddress.title': '{lang}wbb.post.ipAddress.title{/lang}'
			});
		});
		//]]>
	</script>

</head>

<body id="tpl{$templateName|ucfirst}">

	{include file='header'}

	<header class="boxHeadline marginTop wbbThread labeledHeadline">
		<h1>
			{lang}wbb.post.edithistory.versions{/lang} <span class="badge">{#$items}</span>
		</h1>

		{event name='headlineData'}
	</header>

	{include file='userNotice'}

	<div class="contentNavigation">
		{pages print=true assign=pagesLinks application='wbb' controller='PostVersions' id=$post->postID link="pageNo=%d"}

		<nav>
			<ul class="jsThreadInlineEditorContainer">
				{event name='contentNavigationButtonsTop'}
			</ul>
		</nav>
	</div>

	<div class="marginTop">
		<ul class="wbbVersionList versionList">
			{foreach from=$objects item=version}
				{assign var='objectID' value=$version->versionID}
				{assign var='userProfile' value=$version->getUserProfile()}
				<li id="version{$version->versionID}" class="marginTop">
					<article class="wbbPost message messageSidebarOrientationLeft dividers jsMessage">
						{include file='messageSidebar'}
						<div>					
							<section class="messageContent">
								<div>
									<header class="messageHeader">
										<div class="messageHeadline">
											<p>
												<a href="{link application='wbb' controller='PostVersions' id=$post->postID appendSession=false}{/link}#version{@$version->versionID}" class="permalink">{@$version->time|time}</a>
											</p>
										</div>
									</header>


									<div class="messageBody">
										<div>
											<div class="messageText">
												{@$version->getFormattedMessage()}
											</div>

										</div>



										<div class="messageFooter">
										</div>

										<footer class="messageOptions">
											<nav class="jsMobileNavigation buttonGroupNavigation">
												<ul class="smallButtons buttonGroup">
												{if LOG_IP_ADDRESS && $version->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress')}<li class="jsIpAddress jsOnly" data-version-id="{@$version->versionID}"><a title="{lang}wbb.post.edithistory.ipAddress{/lang}" class="button jsTooltip"><span class="icon icon16 icon-globe"></span> <span class="invisible">{lang}wbb.post.edithistory.ipAddress{/lang}</span></a></li>{/if}
											{if $canRestore}<li class="jsOnly jsPostRevert" data-version-id="{@$version->versionID}"><a title="{lang}wbb.post.edithistory.revert{/lang}" class="button jsTooltip"><span class="icon icon16 icon-reply"></span> <span class="invisible">{lang}wbb.post.version.revert{/lang}</span></a></li>{/if}

											{event name='messageOptions'}
										</ul>
									</nav>
								</footer>

							</div>
						</div>
					</section>
				</div>
			</article>
		</li>
	{/foreach}

</ul>
</div>

<div class="contentNavigation">
        {@$pagesLinks}

        {hascontent}
        <nav>
		<ul>
			{content}
			{event name='contentNavigationButtonsBottom'}
			{/content}
		</ul>
        </nav>
        {/hascontent}
</div>

{include file='footer'}

</body>
</html>