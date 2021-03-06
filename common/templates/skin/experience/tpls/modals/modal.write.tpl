 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="modal fade in modal-write" id="modal-write">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.block_create}</h4>
            </header>

            {strip}
                <div class="modal-body">
                    <ul class="list-unstyled list-inline modal-write-list clearfix">
                        {if $iUserCurrentCountTopicDraft}
                            <li class="write-item-type-draft">
                                <a href="{router page='content'}drafts/" class="content-logo link link-lead link-light-gray link-clear">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="{router page='content'}drafts/" class="write-item-link link link-lead link-dark link-clear">{$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:$sLang}</a>
                            </li>
                        {/if}
                        {foreach $aContentTypes as $oContentType}
                            {if $oContentType->isAccessible()}
                                {$sLink=R::GetLink('content')|cat:$oContentType->getContentUrl()|cat:'/add/'}
                                {if $oBlog}{$sLink=$sLink|cat:'?blog_id='|cat:$oBlog->getid()}{/if}
                                <li class="write-item-type-topic">
                                    <a href="{$sLink}" class="content-logo link link-lead link-light-gray link-clear">
                                        <i class="fa fa-file-text-o"></i>
                                    </a>
                                    <a href="{$sLink}" class="write-item-link link link-lead link-dark link-clear">{$oContentType->getContentTitle()|escape:'html'}</a>
                                </li>
                            {/if}
                        {/foreach}
                        <li class="write-item-type-blog">
                            <a href="{router page='blog'}add" class="content-logo link link-lead link-light-gray link-clear">
                                <i class="fa fa-folder-o"></i>
                            </a>
                            <a href="{router page='blog'}add" class="write-item-link link link-lead link-dark link-clear">{$aLang.block_create_blog}</a>
                        </li>
                        <li class="write-item-type-message">
                            <a href="{router page='talk'}add" class="content-logo link link-lead link-light-gray link-clear">
                                <i class="fa fa-envelope-o"></i>
                            </a>
                            <a href="{router page='talk'}add" class="write-item-link link link-lead link-dark link-clear">{$aLang.block_create_talk}</a>
                        </li>
                        {hook run='write_item' isPopup=true}
                    </ul>

                </div>
            {/strip}

        </div>
    </div>
</div>
