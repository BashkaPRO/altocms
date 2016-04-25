<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package actions
 * @since   1.0
 */
class ActionRss extends Action {
    /**
     * Инициализация
     */
    public function init() {

        $this->setDefaultEvent('index');
        R::SetIsShowStats(false);
    }

    /**
     * Указывает браузеру правильный content type в случае вывода RSS-ленты
     */
    protected function initRss() {

        header('Content-Type: application/rss+xml; charset=utf-8');
    }

    /**
     * Регистрация евентов
     */
    protected function registerEvent() {

        $this->addEventPreg('/^index$/', '/^newall$/', 'RssTopics');
        $this->addEventPreg('/^index$/', '/^new$/', 'RssTopics');
        $this->addEventPreg('/^index$/', '/^all$/', 'RssTopics');
        $this->addEvent('index', 'RssTopics');
        $this->addEvent('new', 'RssTopics');
        $this->addEvent('wall', 'RssWall');
        $this->addEvent('allcomments', 'RssComments');
        $this->addEventPreg('/^comments$/', '/^\d+$/', 'RssCommentsByTopic');
        $this->addEvent('tag', 'RssTopics');
        $this->addEvent('blog', 'RssBlog');
        $this->addEvent('personal_blog', 'RssPersonalBlog');
    }

    /**
     * Вывод RSS последних комментариев
     */
    protected function rssWall() {

        $aResult = E::ModuleWall()->getWall(array(), array('date_add' => 'DESC'), 1, Config::Get('module.wall.per_page'));
        /** @var ModuleWall_EntityWall[] $aWall */
        $aWall = $aResult['collection'];

        $aRssChannelData = array(
            'title' => C::Get('view.name'),
            'description' => C::Get('path.root.url') . ' / Wall RSS channel',
            'link' => C::Get('path.root.url'),
            'language' => C::Get('lang.current'),
            'managing_editor' => C::Get('general.rss_editor_mail'),
            'web_master' => C::Get('general.rss_editor_mail'),
            'generator' => 'Alto CMS v.' . ALTO_VERSION,
        );

        /** @var ModuleRss_EntityRssChannel $oRssChannel */
        $oRssChannel = E::GetEntity('ModuleRss_EntityRssChannel', $aRssChannelData);

        /** @var ModuleRss_EntityRss $oRss */
        $oRss = E::GetEntity('Rss');

        if ($aWall) {
            // Adds items into RSS channel
            foreach ($aWall as $oItem) {
                if ($oItem) {
                    $oRssChannel->addItem($oItem->createRssItem());
                }
            }
        }
        $oRss->addChannel($oRssChannel);

        $this->_displayRss($oRss);
    }

    /**
     * Event RssComments
     *
     * @return string
     */
    protected function rssComments() {

        $sEvent = $this->getParam(0);
        $aParams = $this->getParams();
        array_shift($aParams);
        E::ModuleHook()->addHandler('action_after', array($this, 'ShowRssComments'));
        return R::Action('comments', $sEvent, $aParams);
    }

    /**
     * Event RssCommentsByTopic
     *
     * @return string
     */
    protected function rssCommentsByTopic() {

        $sEvent = $this->getParam(0);
        $aParams = $this->getParams();
        array_shift($aParams);
        E::ModuleHook()->addHandler('action_after', array($this, 'ShowRssComments'));
        return R::Action('blog', $sEvent . '.html', $aParams);
    }

    /**
     * Show rss comments by hook
     *
     */
    public function showRssComments() {

        $aComments = E::ModuleViewer()->getTemplateVars('aComments');
        $this->_showRssItems($aComments);
    }

    /**
     * Event RssTopics
     *
     * @return string
     */
    protected function rssTopics() {

        $sEvent = $this->getParam(0);
        $aParams = $this->getParams();
        array_shift($aParams);
        E::ModuleHook()->addHandler('action_after', array($this, 'ShowRssTopics'));
        return R::Action($this->sCurrentEvent, $sEvent, $aParams);
    }

    /**
     * Show rss topics by hook
     *
     */
    public function showRssTopics() {

        $aTopics = E::ModuleViewer()->getTemplateVars('aTopics');
        $this->_showRssItems($aTopics);
    }

    /**
     * Create and show rss channel
     *
     * @param $aItems
     */
    protected function _showRssItems($aItems) {

        $aParts = explode('/', trim(R::Url('path'), '/'), 2);
        if (isset($aParts[1])) {
            $sLink = R::GetPath('/' . $aParts[1]);
        } else {
            $sLink = R::GetPath('/');
        }
        if ($sQuery = R::Url('query')) {
            $sLink .= '?' . $sQuery;
        }

        $aRssChannelData = array(
            'title' => E::ModuleViewer()->getHtmlTitle(),
            'description' => E::ModuleViewer()->getHtmlDescription(),
            'link' => $sLink,
            'language' => C::Get('lang.current'),
            'managing_editor' => C::Get('general.rss_editor_mail'),
            'web_master' => C::Get('general.rss_editor_mail'),
            'generator' => 'Alto CMS v.' . ALTO_VERSION,
        );

        /** @var ModuleRss_EntityRssChannel $oRssChannel */
        $oRssChannel = E::GetEntity('ModuleRss_EntityRssChannel', $aRssChannelData);

        /** @var ModuleRss_EntityRss $oRss */
        $oRss = E::GetEntity('Rss');

        if ($aItems) {
            // Adds items into RSS channel
            foreach ($aItems as $oItem) {
                if ($oItem) {
                    $oRssChannel->addItem($oItem->createRssItem());
                }
            }
        }
        $oRss->addChannel($oRssChannel);

        $this->_displayRss($oRss);
    }

    /**
     * Вывод RSS топиков из блога
     */
    protected function rssBlog() {

        $sBlogUrl = $this->getParam(0);
        $aParams = $this->getParams();
        array_shift($aParams);
        E::ModuleHook()->addHandler('action_after', array($this, 'ShowRssBlog'));

        if ($iMaxItems = intval(C::Get('module.topic.max_rss_count'))) {
            C::Set('module.topic.per_page', $iMaxItems);
        }

        return R::Action('blog', $sBlogUrl, $aParams);
    }

    /**
     * @return null|string
     */
    protected function rssPersonalBlog() {

        $sUserLogin = $this->getParam(0);
        $aParams = $this->getParams();
        array_shift($aParams);

        if ($iMaxItems = intval(C::Get('module.topic.max_rss_count'))) {
            C::Set('module.topic.per_page', $iMaxItems);
        }

        $oUser = E::ModuleUser()->getUserByLogin($sUserLogin);
        if ($oUser && ($oBlog = E::ModuleBlog()->getPersonalBlogByUserId($oUser->getId()))) {
            E::ModuleHook()->addHandler('action_after', array($this, 'ShowRssBlog'));
            return R::Action('blog', $oBlog->getId(), $aParams);
        } else {
            $this->_displayEmptyRss();
        }
        return null;
    }

    /**
     *
     */
    public function showRssBlog() {

        /** @var ModuleTopic_EntityTopic[] $aTopics */
        $aTopics = E::ModuleViewer()->getTemplateVars('aTopics');

        /** @var ModuleBlog_EntityBlog $oBlog */
        $oBlog = E::ModuleViewer()->getTemplateVars('oBlog');

        if ($oBlog) {
            /** @var ModuleRss_EntityRss $oRss */
            $oRss = E::GetEntity('Rss');

            // Creates RSS channel from the blog
            $oRssChannel = $oBlog->createRssChannel();

            if (is_array($aTopics)) {
                // Adds items into RSS channel
                foreach ($aTopics as $oTopic) {
                    $oRssChannel->addItem($oTopic->createRssItem());
                }
            }
            $oRss->addChannel($oRssChannel);

            $this->_displayRss($oRss);
        } else {
            F::HttpResponseCode(404);
            $this->_displayEmptyRss();
        }
    }

    /**
     * @param $oRss
     */
    protected function _displayRss($oRss) {

        E::ModuleViewer()->assign('oRss', $oRss);
        E::ModuleViewer()->setResponseHeader('Content-type', 'text/xml; charset=utf-8');
        E::ModuleViewer()->display('actions/rss/action.rss.index.tpl');

        exit;
    }

    /**
     *
     */
    protected function _displayEmptyRss() {

        $oRss = E::GetEntity('Rss');
        $this->_displayRss($oRss);
    }

}

// EOF