<?php
/**
 * Copyright 2007-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Duck <duck@obala.net>
 */

define('AUTH_HANDLER', true);
define('AGORA_BASE', dirname(__FILE__) . '/../');
require_once AGORA_BASE . '/lib/base.php';

// Detect forum id
$scope = Horde_Util::getGet('scope', 'agora');
$forum_id = Horde_Util::getGet('forum_id');
if ($scope != 'agora') {
    if (($forum_name = Horde_Util::getGet('forum_name')) !== null) {
        $threads = Agora_Messages::singleton($scope);
        $forum_id = $threads->getForumId($forum_name);
        if (($forum_id instanceof PEAR_Error) || empty($forum_id)) {
            die($forum_id);
        }
    } elseif ($forum_id !== null) {
        $threads = Agora_Messages::singleton($scope, $forum_id);
        if ($threads instanceof PEAR_Error) {
            die($threads);
        }
        $forum_array = $threads->getForum();
        $forum_name = $forum_array['forum_name'];
    }
}

$cache_key = 'agora_rss_' . $scope . '_' . $forum_id;

/* Initialize the Cache object. */
$cache = &Horde_Cache::singleton($GLOBALS['conf']['cache']['driver'],
                                    Horde::getDriverConfig('cache', $GLOBALS['conf']['cache']['driver']));

$rss = $cache->get($cache_key, $conf['cache']['default_lifetime']);

if (!$rss) {

    // Get forum title
    $threads = Agora_Messages::singleton($scope, $forum_id);
    if ($threads instanceof PEAR_Error) {
        Horde::fatal($threads, __FILE__, __LINE__);
    }
    if ($scope == 'agora') {
        $forum_array = $threads->getForum();
        if ($forum_array instanceof PEAR_Error) {
            Horde::fatal($forum_array, __FILE__, __LINE__);
        }
        $title = sprintf(_("Threads in %s"), $forum_array['forum_name']);
    } else {
        $title = $registry->callByPackage($scope, 'commentCallback', array($forum_name, 'title'));
        if ($title instanceof PEAR_Error) {
            Horde::fatal($title, __FILE__, __LINE__);
        }
        $title = sprintf(_("Comments on %s"), $title);
    }

    $threads_list = $threads->getThreads(0, false, 'message_modifystamp', 1, true, '', null, 0, 10);

    $rss = '<?xml version="1.0" encoding="' . Horde_Nls::getCharset() . '" ?>
    <rss version="2.0">
        <channel>
        <title>' . htmlspecialchars($title) . '</title>
        <language>' . str_replace('_', '-', strtolower(Horde_Nls::select())) . '</language>
        <lastBuildDate>' . date('r') . '</lastBuildDate>
        <description>' . htmlspecialchars($title) . '</description>
        <link>' . Horde::applicationUrl('index.php', true, -1) . '</link>
        <generator>' . htmlspecialchars($registry->get('name')) . '</generator>';

    // Use commentCallback to get the return link
    // show is not enought as we can have many parameters, like turba source etc
    $url = Horde::applicationUrl('messages/index.php', true, -1);
    if ($scope != 'agora' && $registry->hasMethod('commentCallback', $scope)) {
        $try = $registry->callByPackage($scope, 'commentCallback', array($forum_name, 'link'));
        if ($try instanceof PEAR_Error) {
            die($try->getMessage());
        }
        if (substr($url, 0, 4) == 'http') {
            $url = $try;
        }
    }

    foreach ($threads_list as $thread_id => $thread) {
        $rss .= '
        <item>
            <title>' . htmlspecialchars($thread['message_subject']) . ' </title>
            <description>' . htmlspecialchars(trim($thread['body'])) . ' </description>
            <link>' . Agora::setAgoraId($forum_id, $thread_id, $url, $scope, true) . '</link>
        </item>';
    }

    $rss .= '
    </channel>
    </rss>';

    $cache->set($cache_key, $rss);
}

header('Content-type: text/xml; charset=' . Horde_Nls::getCharset());
echo $rss;
