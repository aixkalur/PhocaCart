<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutI	= new JLayoutFile('image', null, array('component' => 'com_phocacart'));

echo '<div id="ph-pc-wishlist-box" class="pc-wishlist-view'.$this->p->get( 'pageclass_sfx' ).'">';
echo PhocacartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_WISH_LIST')));

if (!empty($this->t['items'])) {

	echo '<div class="'.$this->s['c']['row'].'">';

	echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"><b>'.JText::_('COM_PHOCACART_IMAGE').'</b></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].'"><b>'.JText::_('COM_PHOCACART_PRODUCT').'</b></div>';

	if (isset($this->t['value']['stock']) && $this->t['value']['stock'] == 1)	{
		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"><b>'.JText::_('COM_PHOCACART_AVAILABILITY').'</b></div>';
	} else {
		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"></div>';
	}

	if ($this->t['can_display_price']) {
		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"><b>'.JText::_('COM_PHOCACART_PRICE').'</b></div>';
	} else {
		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"></div>';
	}

	echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"><b>'.JText::_('COM_PHOCACART_ACTION').'</b></div>';

	echo '</div>';



	$count = count($this->t['items']);
	$price = new PhocacartPrice();



	foreach($this->t['items'] as $k => $v) {

		echo '<div class="'.$this->s['c']['row'].'">';

		if (isset($v['catid2']) && (int)$v['catid2'] > 0 && isset($v['catalias2']) && $v['catalias2'] != '') {
			$link 	= JRoute::_(PhocacartRoute::getItemRoute($v['id'], $v['catid2'], $v['alias'], $v['catalias2']));
		} else {
			$link 	= JRoute::_(PhocacartRoute::getItemRoute($v['id'], $v['catid'], $v['alias'], $v['catalias']));
		}

		$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v['image'], 'small');
		$imageO = '';
		if (isset($image->rel) && $image->rel != '') {
			$imageO = '<div class="ph-center" >';
			$imageO .= '<a href="'.$link.'">';

            $d						= array();
            $d['t']					= $this->t;
            $d['s']					= $this->s;
            $d['src']				= JURI::base(true).'/'.$image->rel;
            $d['srcset-webp']		= JURI::base(true).'/'.$image->rel_webp;
            $d['alt-value']			= PhocaCartImage::getAltTitle($v['title'], $image->rel);
            $d['class']				= $this->s['c']['img-responsive'];

            $imageO .= $layoutI->render($d);


			$imageO .= '</a>';
			$imageO .= '</div>';
		}

		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle">'.$imageO.'</div>';

		echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' phVMiddle">'.$v['title'].'</div>';

		if (isset($this->t['value']['stock']) && $this->t['value']['stock'] == 1)	{
			echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle">'.JText::_($v['stock']).'</div>';
		} else {
			echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle"></div>';
		}

		if ($this->t['can_display_price']) {
			echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle">'.$price->getPriceItem($v['price'], $v['group_price']).'</div>';
		} else {
			echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle"></div>';
		}

		echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' phVMiddle">';

		echo '<form action="'.$this->t['linkwishlist'].'" method="post">';
		echo '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		echo '<input type="hidden" name="task" value="wishlist.remove">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
		//echo '<div class="ph-center">';
		echo '<button type="submit" class="'.$this->s['c']['btn.btn-danger'].' ph-btn" title="'.JText::_('COM_PHOCACART_REMOVE').'"><span class="'.$this->s['i']['remove'].'"></span></button>';
		//echo '</div>';


		echo ' ';

		$link = JRoute::_(PhocacartRoute::getItemRoute($v['id'], $v['catid'], $v['alias'], $v['catalias']));
		echo '<a href="'.$link.'" class="'.$this->s['c']['btn.btn-danger'].' ph-btn" role="button" title="'.JText::_('COM_PHOCACART_VIEW_PRODUCT').'"><span class="'.$this->s['i']['search'].'"></span></a>';

		echo JHtml::_('form.token');
		echo '</form>';

		echo '</div>';
		/*
		if ($this->t['value']['attrib'] == 1) 	{
			$c['attrib'] 	.= '<td>';
			if(!empty($v['attr_options'])) {
				foreach ($v['attr_options'] as $k2 => $v2) {
					$c['attrib'] 	.= '<div>'.$v2->title.'</div>';
					if(!empty($v2->options)) {
						$c['attrib'] 	.= '<ul>';
						foreach ($v2->options as $k3 => $v3) {
							$c['attrib'] 	.= '<li>'.$v3->title.'</li>';
						}
						$c['attrib'] 	.= '</ul>';
					}
				}

			}
			$c['attrib'] 	.= '</td>';
		} */


		echo '</div>';// end row
	}


	echo $this->loadTemplate('login');

} else {
	echo '<div class="alert alert-error alert-danger">'.JText::_('COM_PHOCACART_THERE_ARE_NO_PRODUCTS_IN_YOUR_WISH_LIST').'</div>';
}



echo '</div>';// end wishlist box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
