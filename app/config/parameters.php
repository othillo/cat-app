<?php

if (false !== ($storageDir = getenv('STORAGE_DIR'))) {
    $container->setParameter('storage_dir', $storageDir);
}
