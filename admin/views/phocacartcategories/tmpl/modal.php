<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$app = JFactory::getApplication();
if ($app->isClient('site')) {
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_phocacart/administrator/admin-phocacartitems-modal.min.js', array('version' => 'auto', 'relative' => true));


//$class		= $this->t['n'] . 'RenderAdminviews';
$r 			=  new PhocacartRenderAdminviews();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

$function  	= $app->input->getCmd('function', 'jSelectPhocacartcategory');
$onclick   	= $this->escape($function);

if (!empty($editor)) {
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	JFactory::getDocument()->addScriptOptions('xtd-phocacartcategories', array('editor' => $editor));
	$onclick = "jSelectPhocacartcategory";
}

$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);

//echo '<div class="clearfix"></div>';


echo $r->startFormModal($this->t['o'], $this->t['tasks'], 'phocacartcategory-form', 'adminForm', $function);
echo $r->startFilterNoSubmenu();
echo $r->endFilter();

echo $r->startMainContainerNoSubmenu();

if ($this->t['search']) {
	echo '<div class="alert alert-message">' . JText::_('COM_PHOCACART_SEARCH_FILTER_IS_ACTIVE') .'</div>';
}

echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
	$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterLevels('COM_PHOCACART_SELECT_MAX_LEVELS', $this->state->get('filter.level'));
echo $r->endFilterBar();

echo $r->endFilterBar();

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-title">'.JHtml::_('grid.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">'.JHtml::_('grid.sort', $this->t['l'].'_PARENT_CATEGORY', 'parentcat_title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">'.JTEXT::_($this->t['l'].'_ACCESS').'</th>'."\n";
echo '<th class="ph-language">'.JHtml::_('grid.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-hits">'.JHtml::_('grid.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.JHtml::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo '<tbody>'. "\n";

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

			$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
			$orderkey   	= array_search($item->id, $this->ordering[$item->parent_id]);
			$ordering		= ($listOrder == 'a.ordering');
			$canCreate		= $user->authorise('core.create', $this->t['o']);
			$canEdit		= $user->authorise('core.edit', $this->t['o']);
			$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
			$linkEdit 		= JRoute::_( $urlEdit.(int) $item->id );
			$linkParent		= JRoute::_( $urlEdit.(int) $item->parent_id );
			$canEditParent	= 0;//$user->authorise('core.edit', $this->t['o']);
			$linkLang		= JRoute::_('index.php?option='.$this->t['o'].'&view=phocacartcategory&id='.$this->escape($item->id).'&lang='.$this->escape($item->language));

			//$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
			$canEdit	= 0;// FORCE NOT EDITING CATEGORY IN MODAL $user->authorise('core.edit', $this->t['o']);
			if ($item->language && JLanguageMultilang::isEnabled()) {
				$tag = strlen($item->language);
				if ($tag == 5) {
					$lang = substr($item->language, 0, 2);
				} else if ($tag == 6) {
					$lang = substr($item->language, 0, 3);
				} else {
					$lang = '';
				}
			} else if (!JLanguageMultilang::isEnabled()) {
				$lang = '';
			}


			$parentsStr = '';
			if (isset($item->parentstree)) {
				$parentsStr = ' '.$item->parentstree;
			}
			if (!isset($item->level)) {
				$item->level = 0;
			}

			$iD = $i % 2;
			echo "\n\n";

			echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->parent_id.'" item-id="'.$item->id.'" parents="'.$parentsStr.'" level="'. $item->level.'">'. "\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->parent_id.'" >'. "\n";

			echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
			echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
			/*$checkO = '';
			if ($item->checked_out) {
				$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
			}
			if ($canCreate || $canEdit) {
				$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
			} else {
				$checkO .= $this->escape($item->title);
			}
			$checkO .= ' <span class="smallsub">(<span>'.JText::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
			echo $r->td($checkO, "small");
			*/
			$linkBox = '<a class="select-link" href="javascript:void(0)" data-function="'.$this->escape($onclick).'" data-id="'.$item->id.'" data-title="'.$this->escape($item->title).'" data-uri="'. $this->escape($linkLang).'" data-language="'.$this->escape($lang).'">';
			$linkBox .= $this->escape($item->title);
			$linkBox .= '</a>';

			echo $r->td($linkBox, "small");

			//echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

			echo $r->td('<span class="'.$iconStates[$this->escape($item->published)].'" aria-hidden="true"></span>');

			if ($canEditParent) {
				$parentO = '<a href="'. JRoute::_($linkParent).'">'. $this->escape($item->parentcat_title).'</a>';
			} else {
				$parentO = $this->escape($item->parentcat_title);
			}
			echo $r->td($parentO, "small");
			echo $r->td($this->escape($item->access_level), "small");

			//echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
			echo $r->td(JLayoutHelper::render('joomla.content.language', $item));

			echo $r->td($item->hits, "small");
			echo $r->td($item->id, "small");

			echo '</tr>'. "\n";

		}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 9);
echo $r->endTable();

echo $this->loadTemplate('batch');

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
