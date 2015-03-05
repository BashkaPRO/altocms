{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

{if count($aPhotos)}
    <div class="alto-photoset js-topic-photoset-list" {if $sPosition=='left' || $sPosition=='right'}data-width="{$sPosition}"{/if}>{strip}
        {foreach $aPhotos as $oPhoto}
            <a href="{$oPhoto->getWebPath()}" class="topic-photoset-item">
                <img src="{$oPhoto->getWebPath('x240')}"
                     data-rel="prettyPhoto[pp_gal_{$sPhotosetHash}]"
                     alt="{$oPhoto->getDescription()}"/>
            </a>
        {/foreach}
    {/strip}</div>
{/if}
