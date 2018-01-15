<?php

function smarty_modifier_urlfriendly($str) {
    return \Team\Data\Sanitize::urlFriendly($str);
}
