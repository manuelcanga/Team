<?php

function smarty_modifier_urlfriendly($str) {
    return \Team\data\Sanitize::urlFriendly($str);
}
