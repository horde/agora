<?php
/**
 * Agora General View Class
 *
 * Copyright 2007-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Agora
 */
class Agora_View extends Horde_View {

    /**
     * Constructor
     */
    public function __construct()
    {
        /* Set default data. */
        parent::__construct(array('templatePath' => AGORA_TEMPLATES));
    }

}
