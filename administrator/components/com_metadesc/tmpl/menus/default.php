<?php
/**
 * @package         Metadesc
 * @version         2.0.0
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2023 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$menuType  = (string) $app->getUserState('com_menus.items.menutype', '');

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();

$wa->useScript('multiselect');
$wa->useScript('short-and-sweet');
$wa->useScript('metadesc.main');
$wa->useStyle('metadesc.main');

Text::script('COM_METADESC_FIELD_DESCRIPTION');
Text::script('COM_METADESC_FIELD_DESCRIPTION_COUNT');

?>

<form action="<?php echo Route::_('index.php?option=com_metadesc&view=menus'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList" id="articleList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_METADESC_MENUS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 d-none d-sm-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                            </th>
                            <?php if (Multilanguage::isEnabled()) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <?php echo Text::_('JGRID_HEADING_LANGUAGE'); ?>
                                </th>
                            <?php endif; ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_METADESC_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
                            </th>
                            <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <th scope="col" class="w-3 d-none d-lg-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                            <?php $item_params = json_decode($item->params); ?>
                            <?php $meta_description = ''; if (isset($item_params->{'menu-meta_description'})) $meta_description = $item_params->{'menu-meta_description'}; ?>
                            <tr class="row<?php echo $i % 2; ?>" id="metadesc_<?php echo (int) $item->id; ?>">
                                <td class="d-none d-sm-table-cell text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'menus.', false); ?>
                                </td>
                                <td>
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'menus.', false); ?>
                                    <?php else: ?>
                                        <span class="tbody-icon metadesc-cheockout hidden">
                                            <span class="icon-checkedout"></span>
                                        </span>
                                    <?php endif; ?>

                                    <span class="metadesc-title"><?php echo $this->escape($item->title); ?></span>

                                    <div class="metadesc-alias">
                                        <span class="small break-word">
                                            <?php if (empty($item->note)) : ?>
                                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                            <?php else : ?>
                                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <div class="metadesc-category small">
                                        <span><?php echo Text::_('COM_METADESC_HEADING_TYPE') . ': '; ?></span>
                                        <span class="title"><?php echo $this->escape($item->item_type); ?></span>
                                    </div>

                                    <div class="metadesc-control">
                                        <div class="metadesc-control__item metadesc-button<?php if ($item->checked_out) echo ' hidden'; ?>">
                                            <button class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal-metadesc"
                                                    data-bs-id="<?php echo (int) $item->id; ?>"
                                                    data-bs-type="menu"
                                                    onclick="return false;">
                                                <span class="icon-edit"></span>
                                                <span><?php echo Text::_('COM_METADESC_EDIT_DESCRIPTION'); ?></span>
                                            </button>
                                        </div>
                                        <div class="metadesc-control__item">
                                            <?php $badge_color = $meta_description ? 'bg-success' : 'bg-danger'; ?>
                                            <?php $desc_count = mb_strlen($meta_description); ?>
                                            <span class="metadesc-badge badge <?php echo $badge_color; ?>"><?php echo Text::_('COM_METADESC_HEADING_DESCRIPTION'); ?></span>
                                            <span class="metadesc-count badge bg-info"><?php echo $desc_count; ?></span>
                                        </div>
                                    </div>

                                    <?php $meta_description ? $class_descr = '' : $class_descr = ' hidden'; ?>
                                    <div class="metadesc-description<?php echo $class_descr; ?>">
                                        <span class="icon icon-check-circle"></span>
                                        <span class="text"><?php echo $meta_description; ?></span>
                                    </div>
                                </td>
                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell text-center">
                                        <span class="text"><?php echo $this->escape($item->language); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo $this->escape($item->menutype_title ?: ucwords($item->menutype)); ?>
                                </td>
                                <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                    <td class="small d-none d-md-table-cell text-center">
                                        <?php echo $this->escape($item->access_level); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="d-none d-lg-table-cell">
                                    <?php echo (int) $item->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php if ($user->authorise('core.edit', 'com_metadesc')) : ?>
                        <?php echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'modal-metadesc',
                            [
                                'title'  => Text::_('COM_METADESC_EDIT_TITLE'),
                            ],
                            LayoutHelper::render('components.metadesc.administrator.form_metadata', $this)
                        ); ?>
                    <?php endif; ?>

                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>