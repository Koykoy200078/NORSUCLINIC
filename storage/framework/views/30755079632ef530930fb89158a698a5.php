<div>
    <!--[if BLOCK]><![endif]--><?php if($component->debugIsEnabled()): ?>
        <p><strong><?php echo app('translator')->get('Debugging Values'); ?>:</strong></p>

        <!--[if BLOCK]><![endif]--><?php if(! app()->runningInConsole()): ?>
            <div class="mb-4"><?php dump((new \Rappasoft\LaravelLivewireTables\DataTransferObjects\DebuggableData($component))->toArray()); ?></div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Projects\NORSUCLINIC\resources\views/vendor/livewire-tables/includes/debug.blade.php ENDPATH**/ ?>