<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';;
?>

    <script>
        window.setTimeout(function () {
            location.reload();
        }, 5000);
    </script>

<?php echo (new \Certwatch\Generator\HTMLGenerator())
    ->setStore(false)
    ->setResults(
        (new \Certwatch\Runner())
            ->run()
            ->getResults()
    )
    ->generate()
    ->getResult()
;
