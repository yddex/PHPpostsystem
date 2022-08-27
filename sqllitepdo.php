<?php

return new PDO('sqlite:datebase.sqlite', null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);