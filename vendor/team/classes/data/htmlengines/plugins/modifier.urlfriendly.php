<?php

function smarty_modifier_urlfriendly($str) {
    return \team\Sanitize::urlFriendly($str);
}
