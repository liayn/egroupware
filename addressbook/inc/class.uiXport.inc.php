<?php
  /**************************************************************************\
  * phpGroupWare - addressbook                                               *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class uiXport
	{
		var $output = '';
		var $public_functions = array(
			'import' => True,
			'export' => True
		);
		var $bo;
		var $cat;

		var $start;
		var $limit;
		var $query;
		var $sort;
		var $order;
		var $filter;
		var $cat_id;

		function uiXport()
		{
			$this->cat      = CreateObject('phpgwapi.categories');
			$this->bo       = CreateObject('addressbook.boXport',True);
			$this->browser  = CreateObject('phpgwapi.browser');

			$this->start    = $this->bo->start;
			$this->limit    = $this->bo->limit;
			$this->query    = $this->bo->query;
			$this->sort     = $this->bo->sort;
			$this->order    = $this->bo->order;
			$this->filter   = $this->bo->filter;
			$this->cat_id   = $this->bo->cat_id;
		}

		function totpl()
		{
			if(@isset($this->output) && !empty($this->output))
			{
				$GLOBALS['phpgw']->template->set_var('phpgw_body', $this->output,True);
				unset($this->output);
			}
		}

		/* Return a select form element with the categories option dialog in it */
		function cat_option($cat_id='',$notall=False,$java=True,$multiple=False)
		{
			if($java)
			{
				$jselect = ' onChange="this.form.submit();"';
			}
			/* Setup all and none first */
			$cats_link  = "\n" .'<select name="fcat_id'.($multiple?'[]':'').'"' .$jselect . ($multiple ? 'multiple size="3"' : '') . ">\n";
			if(!$notall)
			{
				$cats_link .= '<option value=""';
				if($cat_id=='all')
				{
					$cats_link .= ' selected';
				}
				$cats_link .= '>'.lang('all').'</option>'."\n";
			}

			/* Get global and app-specific category listings */
			$cats_link .= $this->cat->formated_list('select','all',$cat_id,True);
			$cats_link .= '</select>'."\n";
			return $cats_link;
		}

		function import()
		{
			global $convert,$download,$tsvfile,$private,$conv_type,$fcat_id;

			if($convert)
			{
				$buffer = $this->bo->import($tsvfile,$conv_type,$private,$fcat_id);

				if($download == '')
				{
					if($conv_type == 'Debug LDAP' || $conv_type == 'Debug SQL' )
					{
						// filename, default application/octet-stream, length of file, default nocache True
						$GLOBALS['phpgw']->browser->content_header($tsvfilename,'',strlen($buffer));
						echo $buffer;
					}
					else
					{
						$GLOBALS['phpgw']->common->phpgw_header();
						echo "<pre>$buffer</pre>";
						echo '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.index') . '">'.lang('OK').'</a>';
					}
				}
				else
				{
					$GLOBALS['phpgw']->common->phpgw_header();
					echo "<pre>$buffer</pre>";
					echo '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.index'). '">'.lang('OK').'</a>';
				}
			}
			else
			{
				$GLOBALS['phpgw']->common->phpgw_header();

				set_time_limit(0);

				$GLOBALS['phpgw']->template->set_file(array('import' => 'import.tpl'));

				$dir_handle = opendir(PHPGW_APP_INC . SEP . 'import');
				$i=0; $myfilearray = '';
				while($file = readdir($dir_handle))
				{
					if((substr($file, 0, 1) != '.') && is_file(PHPGW_APP_INC . SEP . 'import' . SEP . $file) )
					{
						$myfilearray[$i] = $file;
						$i++;
					}
				}
				closedir($dir_handle);
				sort($myfilearray);
				for($i=0;$i<count($myfilearray);$i++)
				{
					$fname = ereg_replace('_',' ',$myfilearray[$i]);
					$conv .= '<OPTION VALUE="' . $myfilearray[$i].'">' . $fname . '</OPTION>';
				}

				$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
				$GLOBALS['phpgw']->template->set_var('lang_cat',lang('Select Category'));
				$GLOBALS['phpgw']->template->set_var('cancel_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.index'));
				$GLOBALS['phpgw']->template->set_var('conversion',lang('Select the type of conversion'));
				$GLOBALS['phpgw']->template->set_var('export_path',lang('Enter the path to the export file here'));
				$GLOBALS['phpgw']->template->set_var('navbar_bg',$GLOBALS['phpgw_info']['theme']['navbar_bg']);
				$GLOBALS['phpgw']->template->set_var('navbar_text',$GLOBALS['phpgw_info']['theme']['navbar_text']);
				$GLOBALS['phpgw']->template->set_var('mark_private',lang('Mark records as private'));
				$GLOBALS['phpgw']->template->set_var('help_import',lang('In Netscape, open the Addressbook and select <b>Export</b> from the <b>File</b> menu.<br>The file exported will be in LDIF format.<P>Or, in Outlook, select your Contacts folder, select <b>Import and Export...</b> from'));
				$GLOBALS['phpgw']->template->set_var('help_import2',lang('the <b>File</b> menu and export your contacts into a comma separated text (CSV) file. <P>Or, in Palm Desktop 4.0 or greater, visit your addressbook and select <b>Export</b> from the <b>File</b> menu. The file exported will be in VCard format.<P>'));
				$GLOBALS['phpgw']->template->set_var('none',lang('none'));
				$GLOBALS['phpgw']->template->set_var('debug_browser',lang('Debug output in browser'));
				$GLOBALS['phpgw']->template->set_var('import_text',lang('Import from LDIF, CSV, or VCard'));
				$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.import'));
				$GLOBALS['phpgw']->template->set_var('cat_link',$this->cat_option($this->cat_id,True,False));
				$GLOBALS['phpgw']->template->set_var('tsvfilename','');
				$GLOBALS['phpgw']->template->set_var('conv',$conv);
				$GLOBALS['phpgw']->template->set_var('debug',lang('Debug output in browser'));
				$GLOBALS['phpgw']->template->set_var('filetype',lang('LDIF'));
				$GLOBALS['phpgw']->template->set_var('download',lang('Submit'));
				$GLOBALS['phpgw']->template->set_var('start',$this->start);
				$GLOBALS['phpgw']->template->set_var('sort',$this->sort);
				$GLOBALS['phpgw']->template->set_var('order',$this->order);
				$GLOBALS['phpgw']->template->set_var('filter',$this->filter);
				$GLOBALS['phpgw']->template->set_var('query',$this->query);
				$GLOBALS['phpgw']->template->set_var('cat_id',$this->cat_id);

				$this->output = $GLOBALS['phpgw']->template->fp('out','import');
				$this->totpl();
			}
		}

		function export()
		{
			global $convert,$tsvfilename,$cat_id,$download,$conv_type;

			if($convert)
			{
				if($conv_type == 'none')
				{
					$GLOBALS['phpgw_info']['flags']['noheader'] = False;
					$GLOBALS['phpgw_info']['flags']['noheader'] = True;
					$GLOBALS['phpgw']->common->phpgw_header();
					echo lang('<b>No conversion type &lt;none&gt; could be located.</b>  Please choose a conversion type from the list');
					echo '&nbsp<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.export') . '">' . lang('OK') . '</a>';
					$GLOBALS['phpgw_info']['flags']['nodisplay'] = True;
					exit;
				}

				$buffer = $this->bo->export($conv_type,$cat_id);

				if(($download == 'on') || ($conv_type == 'Palm_PDB'))
				{
					// filename, default application/octet-stream, length of file, default nocache True
					$this->browser->content_header($tsvfilename,'application/x-octet-stream',strlen($buffer));
					echo $buffer;
					exit;
				}
				else
				{
					$GLOBALS['phpgw']->common->phpgw_header();
					echo "<pre>\n";
					echo $buffer;
					echo "\n</pre>\n";
					echo '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.export') . '">' . lang('OK') . '</a>';
				}
			}
			else
			{
				$GLOBALS['phpgw']->common->phpgw_header();

				set_time_limit(0);

				$GLOBALS['phpgw']->template->set_file(array('export' => 'export.tpl'));

				$dir_handle = opendir(PHPGW_APP_INC. SEP . 'export');
				$i=0; $myfilearray = '';
				while($file = readdir($dir_handle))
				{
					if((substr($file, 0, 1) != '.') && is_file(PHPGW_APP_INC . SEP . 'export' . SEP . $file) )
					{
						$myfilearray[$i] = $file;
						$i++;
					}
				}
				closedir($dir_handle);
				sort($myfilearray);
				for($i=0;$i<count($myfilearray);$i++)
				{
					$fname = ereg_replace('_',' ',$myfilearray[$i]);
					$conv .= '        <option value="'.$myfilearray[$i].'">'.$fname.'</option>'."\n";
				}

				$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
				$GLOBALS['phpgw']->template->set_var('lang_cat',lang('Select Category'));
				$GLOBALS['phpgw']->template->set_var('cat_link',$this->cat_option($this->cat_id,False,False));
				$GLOBALS['phpgw']->template->set_var('cancel_url',$GLOBALS['phpgw']->link('/addressbook/index.php'));
				$GLOBALS['phpgw']->template->set_var('navbar_bg',$GLOBALS['phpgw_info']['theme']['navbar_bg']);
				$GLOBALS['phpgw']->template->set_var('navbar_text',$GLOBALS['phpgw_info']['theme']['navbar_text']);
				$GLOBALS['phpgw']->template->set_var('export_text',lang('Export from Addressbook'));
				$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.export'));
				$GLOBALS['phpgw']->template->set_var('filename',lang('Export file name'));
				$GLOBALS['phpgw']->template->set_var('conversion',lang('Select the type of conversion'));
				$GLOBALS['phpgw']->template->set_var('conv',$conv);
				$GLOBALS['phpgw']->template->set_var('debug',lang(''));
				$GLOBALS['phpgw']->template->set_var('download',lang('Submit'));
				$GLOBALS['phpgw']->template->set_var('download_export',lang('Download export file (Uncheck to debug output in browser)'));
				$GLOBALS['phpgw']->template->set_var('none',lang('none'));
				$GLOBALS['phpgw']->template->set_var('start',$this->start);
				$GLOBALS['phpgw']->template->set_var('sort',$this->sort);
				$GLOBALS['phpgw']->template->set_var('order',$this->order);
				$GLOBALS['phpgw']->template->set_var('filter',$this->filter);
				$GLOBALS['phpgw']->template->set_var('query',$this->query);
				$GLOBALS['phpgw']->template->set_var('cat_id',$this->cat_id);

				$this->output = $GLOBALS['phpgw']->template->fp('out','export');
				$this->totpl();
			}
		}
	}
?>
