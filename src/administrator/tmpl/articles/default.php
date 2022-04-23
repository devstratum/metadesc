<?php
/**
 * @package         Metadesc Component
 * @version         1.00
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
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

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();

$wa->useScript('multiselect');
$wa->useScript('short-and-sweet');
$wa->useScript('metadesc.main');
$wa->useStyle('metadesc.main');

Text::script('COM_METADESC_FIELD_DESCRIPTION');
Text::script('COM_METADESC_FIELD_DESCRIPTION_COUNT');

?>

<form action="<?php echo Route::_('index.php?option=com_metadesc&view=articles'); ?>" method="post" name="adminForm" id="adminForm">
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
                            <?php echo Text::_('COM_METADESC_ARTICLES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 d-none d-sm-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
                            </th>
                            <?php if (Multilanguage::isEnabled()) : ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                            </th>
                            <?php endif; ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_METADESC_HEADING_DATE_CREATED', 'a.created', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-3 d-none d-lg-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                            <tr class="row<?php echo $i % 2; ?>" id="metadesc_<?php echo (int) $item->id; ?>">
                                <td class="d-none d-sm-table-cell text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'articles.', false); ?>
                                </td>
                                <td>
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', false); ?>
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
                                        <span><?php echo Text::_('JCATEGORY') . ': '; ?></span>
                                        <span class="title"><?php echo $this->escape($item->category_title); ?></span>
                                    </div>

                                    <div class="metadesc-control">
                                        <div class="metadesc-control__item metadesc-button<?php if ($item->checked_out) echo ' hidden'; ?>">
                                            <button class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal-metadesc"
                                                    data-bs-id="<?php echo (int) $item->id; ?>"
                                                    data-bs-type="article"
                                                    onclick="return false;">
                                                <span class="icon-edit"></span>
                                                <span><?php echo Text::_('COM_METADESC_EDIT_DESCRIPTION'); ?></span>
                                            </button>
                                        </div>
                                        <div class="metadesc-control__item">
                                            <?php $badge_color = $this->escape($item->metadesc) ? 'bg-success' : 'bg-danger'; ?>
                                            <?php $desc_count = mb_strlen($this->escape($item->metadesc)); ?>
                                            <span class="metadesc-badge badge <?php echo $badge_color; ?>"><?php echo Text::_('COM_METADESC_HEADING_DESCRIPTION'); ?></span>
                                            <span class="metadesc-count badge bg-info"><?php echo $desc_count; ?></span>
                                        </div>
                                    </div>

                                    <?php $this->escape($item->metadesc) ? $class_descr = '' : $class_descr = ' hidden'; ?>
                                    <div class="metadesc-description<?php echo $class_descr; ?>">
                                        <span class="icon icon-check-circle"></span>
                                        <span class="text"><?php echo $this->escape($item->metadesc); ?></span>
                                    </div>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php if ((int) $item->author_id != 0) : ?>
                                        <?php echo $this->escape($item->author_name); ?>
                                    <?php else : ?>
                                        <?php echo Text::_('JNONE'); ?>
                                    <?php endif; ?>
                                </td>

                                <?php if (Multilanguage::isEnabled()) : ?>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                </td>
                                <?php endif; ?>

                                <td class="small d-none d-md-table-cell text-center">
                                    <?php
                                    $date = $item->created;
                                    echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
                                    ?>
                                </td>
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