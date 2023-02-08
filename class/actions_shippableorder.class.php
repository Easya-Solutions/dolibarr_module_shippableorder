<?php
class ActionsShippableorder
{
     /** Overloading the doActions function : replacing the parent's function with the one below
      *  @param      parameters  meta datas of the hook (context, etc...)
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
      *  @param      action             current action (if set). Generally create or edit or null
      *  @return       void
      */

    function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
      	global $db, $langs;

		if (in_array('ordercard',explode(':',$parameters['context'])) && $object->statut < 3 && $object->id > 0)
        {
        	dol_include_once('/shippableorder/class/shippableorder.class.php');

			$shippableOrder = new ShippableOrder($db);
			$shippableOrder->isOrderShippable($object->id);
        	echo '<tr><td>'.$langs->trans('ShippableStatus').'</td>';
			echo '<td>'.$shippableOrder->orderStockStatus(false).'</td></tr>';
			$object->shippableorder = $shippableOrder;
        }

		return 0;
	}

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
      	global $db, $langs, $conf;

		if (in_array('ordercard',explode(':',$parameters['context'])) && $object->statut < 3)
        {
            if (!empty($object->lines)) {
                dol_include_once('/shippableorder/class/shippableorder.class.php');
                include_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

                $shippableOrder =  &$object->shippableorder;
                $form = new Form($db);
                $virtualTooltip = ShippableOrder::prepareTooltip();
            	$textColor = !empty($conf->global->THEME_ELDY_TEXTTITLE) ? $conf->global->THEME_ELDY_TEXTTITLE : '';
                $jsConf = array(
                    'textColor' => $textColor,
                    'TheoreticalStockLabel' => $form->textwithpicto($langs->trans('TheoreticalStock'), $virtualTooltip),
                    'RealStock' => $langs->trans('RealStock')
                );

                ?>
                <script type="text/javascript">
                    let jsConf = <?php echo  json_encode($jsConf); ?>;
                    // From version 17 of Dolibarr and on other themes, the first column lines use a "th" tag instead of "td".
                    // With the method below that allows to detect the "th" tag or the "td", we cover all versions and themes without fixing a version:
                    let colTag = 'th';
                    let lineColDescription = $('table#tablelines tr.liste_titre '+colTag+'.linecoldescription');
                    if (lineColDescription.length == 0) {
                        colTag = 'td';
                        lineColDescription = $('table#tablelines tr.liste_titre '+colTag+'.linecoldescription');
                    }
                    lineColDescription.first().after('<'+colTag+' class="linecolstock" align="right" style="color:'+jsConf.textColor+';">'+jsConf.TheoreticalStockLabel+'</'+colTag+'><'+colTag+' class="linecolstock" align="right" style="color:'+jsConf.textColor+';">'+jsConf.RealStock+'</'+colTag+'>');

                    <?php
                    foreach($object->lines as &$line) {

                        $stock = $shippableOrder->orderLineStockStatus($line,true);

                        ?>
                        $('table#tablelines tr[id=row-<?php echo $line->id; ?>] td.linecoldescription').after("<td class=\"linecolstockvirtual nowrap\" align=\"right\"><?php echo addslashes($stock[1]) ?></td><td class=\"linecolstock nowrap\" align=\"right\"><?php echo addslashes($stock[0]) ?></td>");
                        <?php
                    } ?>
                    $('table#tablelines tr.liste_titre_add td.linecoldescription').first().after('<td class="linecolstockvirtual" align="right"></td><td class="linecolstock" align="right"></td>');
                    $('table#tablelines tr.liste_titre_add').next().children('td.linecoldescription').first().after('<td class="linecolstockvirtual" align="right"></td><td class="linecolstock" align="right"></td>');

                    $('table#tablelines tr.liste_titre_create td.linecoldescription').first().after('<td class="linecolstockvirtual nobottom" align="right"></td><td class="linecolstock nobottom" align="right"></td>');
                    $('table#tablelines tr.liste_titre_create').next().children('td.linecoldescription').first().after('<td class="linecolstockvirtual nobottom" align="right"></td><td class="linecolstock nobottom" align="right"></td>');
                    $('#trlinefordates td:first').after('<td class="linecolstockvirtual" align="right"></td><td class="linecolstock" align="right"></td>'); // Add empty column in objectline_create
                    if($('tr[id^="extrarow"]').length > 0) $('tr[id^="extrarow"] td:first').after('<td class="linecolstockvirtual" align="right"></td<td class="linecolstock" align="right"></td>');
                </script>
			<?php
            }
		}

	}

}
