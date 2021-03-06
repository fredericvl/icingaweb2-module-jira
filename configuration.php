<?php

/** @var \Icinga\Application\Modules\Module $this */
$section = $this->menuSection(N_('Jira'))
    ->setUrl('jira')
    ->setPriority(63)
    ->setIcon('tasks');
$section->add(N_('Issues'))->setUrl('jira/issues')->setPriority(10);
if ($this->app->getModuleManager()->hasEnabled('director')) {
    $section->add(N_('Configuration'))
        ->setUrl('jira/configuration/director')
        ->setPriority(20)
        ->setPermission('director/admin');
}
$this->providePermission('jira/issue/create', $this->translate('Allow to manually create issues'));
