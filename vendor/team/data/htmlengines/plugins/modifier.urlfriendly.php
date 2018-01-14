<?php

function smarty_modifier_urlfriendly($str) {
    return \team\data\Sanitize::urlFriendly($str);
}
