<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * Why Items View or why category view? Category view always has category ID,
 * but items view is here for filtering and searching and this can be without category ID
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class PhocaCartViewCategory extends JViewLegacy
{
	protected $category;
	protected $subcategories;
	protected $items;
	protected $t;
	protected $p;

	function display($tpl = null) {

		$app						= JFactory::getApplication();
		$this->p 					= $app->getParams();
		$uri 						= \Joomla\CMS\Uri\Uri::getInstance();
		$model						= $this->getModel();
		$document					= JFactory::getDocument();
		$this->t['categoryid']		= $app->input->get( 'id', 0, 'int' );
		$this->t['limitstart']		= $app->input->get( 'limitstart', 0, 'int' );
		$this->t['ajax'] 			= 0;





		// PARAMS
		$this->t['display_new']				= $this->p->get( 'display_new', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		//$this->t['description']			= $this->p->get( 'description', '' );
		$this->t['cv_display_description']	= $this->p->get( 'cv_display_description', 1 );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );
		//$this->t['image_link']			= $this->p->get( 'image_link', 0 );
		$this->t['columns_cat']				= $this->p->get( 'columns_cat', 3 );
		$this->t['columns_subcat_cat']		= $this->p->get( 'columns_subcat_cat', 3 );
		$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
		$this->t['cv_display_subcategories']= $this->p->get( 'cv_display_subcategories', 5 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		= $this->p->get( 'display_wishlist', 0 );
		$this->t['display_quickview']		= $this->p->get( 'display_quickview', 0 );
		$this->t['display_addtocart_icon']	= $this->p->get( 'display_addtocart_icon', 0 );
		$this->t['fade_in_action_icons']	= $this->p->get( 'fade_in_action_icons', 0 );
		$this->t['category_addtocart']		= $this->p->get( 'category_addtocart', 1 );

		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
		$this->t['dynamic_change_stock']	= $this->p->get( 'dynamic_change_stock', 0 );
		$this->t['add_compare_method']		= $this->p->get( 'add_compare_method', 0 );

		$this->t['add_wishlist_method']		= $this->p->get( 'add_wishlist_method', 0 );


		$this->t['display_star_rating']		= $this->p->get( 'display_star_rating', 0 );
		$this->t['add_cart_method']			= $this->p->get( 'add_cart_method', 0 );
		$this->t['hide_attributes_category']= $this->p->get( 'hide_attributes_category', 1 );
		$this->t['hide_attributes']			= $this->p->get( 'hide_attributes', 0 );
		$this->t['display_stock_status']	= $this->p->get( 'display_stock_status', 1 );
		$this->t['hide_add_to_cart_stock']	= $this->p->get( 'hide_add_to_cart_stock', 0 );
		$this->t['zero_attribute_price']	= $this->p->get( 'zero_attribute_price', 1 );
		$this->t['hide_add_to_cart_zero_price']	= $this->p->get( 'hide_add_to_cart_zero_price', 0 );
		$this->t['cv_subcategories_layout']	= $this->p->get( 'cv_subcategories_layout', 1 );


		// Rights or catalogue options --------------------------------
		$rights								= new PhocacartAccessRights();
		$this->t['can_display_price']		= $rights->canDisplayPrice();
		$this->t['can_display_addtocart']	= $rights->canDisplayAddtocart();
		$this->t['can_display_attributes']	= $rights->canDisplayAttributes();

		if (!$this->t['can_display_addtocart']) {
			$this->t['category_addtocart']		= 0;
			$this->t['display_addtocart_icon'] 	= 0;
			//$this->t['hide_attributes_category']= 1; Should be displayed or not?
		}
		if (!$this->t['can_display_attributes']) {
			$this->t['hide_attributes_category'] = 1;
		}
		// ------------------------------------------------------------

		$this->t['display_view_product_button']	= $this->p->get( 'display_view_product_button', 1 );
		$this->t['product_name_link']			= $this->p->get( 'product_name_link', 0 );
		$this->t['switch_image_category_items']	= $this->p->get( 'switch_image_category_items', 0 );


		$this->t['lazy_load_category_items']	= $this->p->get( 'lazy_load_category_items', 0 );
		$this->t['medium_image_width']			= $this->p->get( 'medium_image_width', 300 );
		$this->t['medium_image_height']			= $this->p->get( 'medium_image_height', 200 );
		$this->t['display_webp_images']			= $this->p->get( 'display_webp_images', 0 );




		$this->category						= $model->getCategory($this->t['categoryid']);

		if (empty($this->category)) {
			header("HTTP/1.0 404 ".JText::_('COM_PHOCACART_NO_CATEGORY_FOUND'));
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_NO_CATEGORY_FOUND').'</div>';
		} else {
			$this->subcategories		= $model->getSubcategories($this->t['categoryid']);
			$this->items				= $model->getItemList($this->t['categoryid']);
			$this->t['pagination']		= $model->getPagination($this->t['categoryid']);
			$this->t['ordering']		= $model->getOrdering();
			$this->t['layouttype']		= $model->getLayoutType();

			$this->t['layouttypeactive'] 	= PhocacartRenderFront::setActiveLayoutType($this->t['layouttype']);
			$this->t['columns_cat'] 		= $this->t['layouttype'] == 'grid' ? $this->t['columns_cat'] : 1;
			$this->t['action']				= $uri->toString();
			//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= JRoute::_(PhocacartRoute::getCheckoutRoute(0, (int)$this->t['categoryid']));
			$this->t['linkcomparison']		= JRoute::_(PhocacartRoute::getComparisonRoute(0, (int)$this->t['categoryid']));
			$this->t['linkwishlist']		= JRoute::_(PhocacartRoute::getWishListRoute(0, (int)$this->t['categoryid']));
			$this->t['limitstarturl'] 		= $this->t['limitstart'] > 0 ? '&start='.$this->t['limitstart'] : '';

			$media = new PhocacartRenderMedia();
			$media->loadBootstrap();
			$media->loadChosen();
			$this->t['class-row-flex'] 	= $media->loadEqualHeights();
			$this->t['class_thumbnail'] = $media->loadProductHover();
            $this->t['class_lazyload']  = $media->loadLazyLoad();

			PhocacartRenderJs::renderAjaxAddToCart();
			PhocacartRenderJs::renderAjaxAddToCompare();
			PhocacartRenderJs::renderAjaxAddToWishList();
			PhocacartRenderJs::renderSubmitPaginationTopForm($this->t['action'], '#phItemsBox');

			$media->loadTouchSpin('quantity');

			if ($this->t['hide_attributes_category'] == 0) {
				$media->loadPhocaAttributeRequired(1); // Some of the attribute can be required and can be a image checkbox
			}

			if ($this->t['dynamic_change_price'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductPriceByOptions(0, 'Category', 'ph-category-price-box');// We need to load it here
			}
			if ($this->t['dynamic_change_stock'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductStockByOptions(0, 'Category', 'ph-item-stock-box');
			}

			// CHANGE PRICE FOR ITEM QUICK VIEW
			if ($this->t['display_quickview'] == 1) {
				PhocacartRenderJs::renderAjaxQuickViewBox();

				// CHANGE PRICE FOR ITEM QUICK VIEW
				if ($this->t['dynamic_change_price'] == 1) {
					PhocacartRenderJs::renderAjaxChangeProductPriceByOptions(0, 'ItemQuick', 'ph-item-price-box');// We need to load it here
				}
				if ($this->t['dynamic_change_stock'] == 1) {
					PhocacartRenderJs::renderAjaxChangeProductStockByOptions(0, 'ItemQuick', 'ph-item-stock-box');
				}
				$media->loadPhocaAttribute(1);// We need to load it here
				$media->loadPhocaSwapImage($this->t['dynamic_change_image']);// We need to load it here in ITEM (QUICK VIEW) VIEW
			}

			$media->loadPhocaMoveImage($this->t['switch_image_category_items']);// Move (switch) images in CATEGORY, ITEMS VIEW

			$this->_prepareDocument();
			$this->t['pathcat'] = PhocacartPath::getPath('categoryimage');
			$this->t['pathitem'] = PhocacartPath::getPath('productimage');

			$model->hit((int)$this->t['categoryid']);

			// Plugins ------------------------------------------
			JPluginHelper::importPlugin('pcv');
			//$this->t['dispatcher']	= J EventDispatcher::getInstance();
			$this->t['event']		= new stdClass;
			$results = \JFactory::getApplication()->triggerEvent('PCVonCategoryBeforeHeader', array('com_phocacart.category', &$this->items, &$this->p));
			$this->t['event']->onCategoryBeforeHeader = trim(implode("\n", $results));
			// Foreach values are rendered in default foreaches
			// END Plugins --------------------------------------

			parent::display($tpl);
		}
	}


	protected function _prepareDocument() {
		$category = false;
		if (isset($this->category[0]) && is_object($this->category[0])) {
			$category = $this->category[0];
		}
		PhocacartRenderFront::prepareDocument($this->document, $this->p, $category);
	}
}
?>
