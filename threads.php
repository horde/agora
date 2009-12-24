<?php
/**
 * The Agora script to display a list of threads in a forum.
 *
 * Copyright 2003-2009 The Horde Project (http://www.horde.org/)
 *
 * $Horde: agora/threads.php,v 1.65 2009-12-10 17:42:30 jan Exp $
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Jan Schneider <jan@horde.org>
 * @author Marko Djukic <marko@oblo.com>
 */

define('AGORA_BASE', dirname(__FILE__));
require_once AGORA_BASE . '/lib/base.php';
require_once AGORA_BASE . '/lib/Messages.php';

/* Make sure we have a forum id. */
list($forum_id, , $scope) = Agora::getAgoraId();
if (empty($forum_id)) {
    header('Location: ' . Horde::applicationUrl('forums.php', true));
    exit;
}

/* Check if this is a valid thread, otherwise show the forum list. */
$threads = &Agora_Messages::singleton($scope, $forum_id);
if ($threads instanceof PEAR_Error) {
    $notification->push(sprintf(_("Could not list threads. %s"), $threads->getMessage()), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('forums.php', true));
    exit;
}

/* Which thread page are we on?  Default to page 0. */
$thread_page = Horde_Util::getFormData('thread_page', 0);
$threads_per_page = $prefs->getValue('threads_per_page');
$thread_start = $thread_page * $threads_per_page;

/* Get the forum data. */
$forum_array = $threads->getForum();

/* Get the sorting. */
$sort_by = Agora::getSortBy('threads');
$sort_dir = Agora::getSortDir('threads');

/* Get a list of threads. */
$threads_list = $threads->getThreads(0, false, $sort_by, $sort_dir, false, '', null, $thread_start, $threads_per_page);
if ($threads_list instanceof PEAR_Error) {
    $notification->push($threads_list->getMessage(), 'horde.error');
    header('Location: ' . Horde::applicationUrl('forums.php', true));
    exit;
} elseif (empty($threads_list)) {
    $threads_count = 0;
} else {
    $threads_count = $threads->_forum['thread_count'];
}
/* Set up the column headers. */
$col_headers = array('message_subject' => _("Subject"), 'message_seq' => _("Posts"), 'view_count' => _("Views"), 'message_author' => _("Started"), 'message_modifystamp' => _("Last post"));
$col_headers = Agora::formatColumnHeaders($col_headers, $sort_by, $sort_dir, 'threads');

/* Set up the template tags. */
$view = new Agora_View();
$view->col_headers = $col_headers;
$view->threads = $threads_list;
$view->forum_name = sprintf(_("Threads in %s"), $forum_array['forum_name']);
$view->forum_description =  Agora_Messages::formatBody($forum_array['forum_description']);
$view->actions = $threads->getThreadActions();
$view->menu = Agora::getMenu('string');
$view->notify = Horde_Util::bufferOutput(array($notification, 'notify'), array('listeners' => 'status'));
$view->rss = Horde_Util::addParameter(Horde::applicationUrl('rss/threads.php', true, -1), array('scope' => $scope, 'forum_id' => $forum_id));

/* Set up pager. */
$vars = Horde_Variables::getDefaultVariables();
$pager_ob = new Horde_Ui_Pager('thread_page', $vars, array('num' => $threads_count, 'url' => 'threads.php', 'perpage' => $threads_per_page));
$pager_ob->preserve('agora', Horde_Util::getFormData('agora'));
$view->pager_link = $pager_ob->render();

$title = sprintf(_("Threads in %s"), $forum_array['forum_name']);
require AGORA_TEMPLATES . '/common-header.inc';
echo $view->render('threads.html.php');
require $registry->get('templates', 'horde') . '/common-footer.inc';
