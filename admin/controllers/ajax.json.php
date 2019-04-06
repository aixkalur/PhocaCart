<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * The contact controller for ajax requests
 *
 * @since  3.9.0
 */
class PhocaCartCpControllerAjax extends JControllerLegacy
{
	/**
	 * Method to fetch associations of a contact
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * token: the form token
	 * assocId: the id of the contact whose associations are to be returned
	 * excludeLang: the association for this language is to be excluded
	 *
	 * @return  null
	 *
	 * @since  3.9.0
	 */
	public function fetchAssociations()
	{


		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$input = JFactory::getApplication()->input;

			$assocId = $input->getInt('assocId', 0);
			$view = $input->get('view', '');



			if ($assocId == 0)
			{
				echo new JResponseJson(null, JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'assocId'), true);

				return;
			}

			$excludeLang = $input->get('excludeLang', '', 'STRING');

            if ($view == 'phocacartcategory') {
                $associations = JLanguageAssociations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', (int)$assocId, 'id', 'alias', false);
            } else if ($view == 'phocacartitem') {

                $associations = JLanguageAssociations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', (int)$assocId, 'id', 'alias', false);
            } else {
                echo new JResponseJson(null, JText::_('COM_PHOCACART_ERROR_NO_VIEW_SET'), true);
                return;
            }


			unset($associations[$excludeLang]);



			// Add the title to each of the associated records
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/tables');
			$table = JTable::getInstance('PhocaCartItem', 'Table');

            if ($view == 'phocacartcategory') {
                $table = JTable::getInstance('PhocaCartItem', 'Table');
            } else if ($view == 'phocacartitem') {
                $table = JTable::getInstance('PhocaCartCategory', 'Table');
            } else {
                echo new JResponseJson(null, JText::_('COM_PHOCACART_ERROR_NO_VIEW_SET'), true);
                return;
            }

			foreach ($associations as $lang => $association)
			{
				$table->load($association->id);
				$associations[$lang]->title = $table->title;
			}

			$countContentLanguages = count(LanguageHelper::getContentLanguages(array(0, 1)));


			if (count($associations) == 0)
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_NONE');
			}
			elseif ($countContentLanguages > count($associations) + 2)
			{
				$tags    = implode(', ', array_keys($associations));
				$message = JText::sprintf('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_SOME', $tags);
			}
			else
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_ALL');
			}

			echo new JResponseJson($associations, $message);
		}
	}
}
