<?php

# We love indexes!
$SQL[] = "ALTER TABLE sessions ADD INDEX ip_address (ip_address);";
$SQL[] = "ALTER TABLE twitter_connect ADD PRIMARY KEY (t_key);";
