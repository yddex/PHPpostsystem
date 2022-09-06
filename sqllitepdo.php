<?php

return new PDO('sqlite:blog.sqlite', null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);