<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'smartledmessenger');
$eqLogics = eqLogic::byType('smartledmessenger');
?>
<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4" id="hidCol" style="display: none;">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-12 eqLogicThumbnailDisplay" id="listCol">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer logoPrimary">

      <div class="cursor eqLogicAction logoSecondary" data-action="add">
          <i class="fas fa-plus-circle"></i>
          <br/>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br/>
        <span>{{Configuration}}</span>
      </div>

    </div>

    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />

    <legend><i class="fas fa-home" id="butCol"></i> {{Mes Equipements}}</legend>
    <div class="eqLogicThumbnailContainer">
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/smartledmessenger/plugin_info/smartledmessenger_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>

	<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
		<a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
		<a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>


		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (jeeObject::all() as $object) {
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Catégorie}}</label>
							<div class="col-sm-10">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>

							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Adresse}}</label>
							<div class="col-sm-3">
								<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addr" type="text" placeholder="{{Adresse IP}}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Type}}</label>
							<div class="col-sm-3">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" id="type">
									<option value="smartledmessenger" selected>Smart Led Messenger</option>
									<option value="notifheure">Notif'Heure</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Intensité}}</label>
							<div class="col-sm-3">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="intensity">
									<?php
									//0 à 15
									$i = 0;
									while ($i <= 15) {
										echo '<option value="' . $i . '">' . $i . '</option>';
										$i++;
									}
									?>
								</select>
							</div>
						</div>

						<div class="form-group" id="vitesse">
							<label class="col-sm-2 control-label">{{Vitesse}}</label>
							<div class="col-sm-3">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="speed">
									<?php
									// 10 à 50
									$i = 10;
									while ($i <= 100) {
										echo '<option value="' . $i . '">' . $i . '</option>';
										$i++;
									}
									?>
								</select>
							</div>
						</div>

						<div class="form-group" id="effect">
							<label class="col-sm-2 control-label">{{Effet}}</label>
							<div class="col-sm-3">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="effect">
									<option value="BLINDS">Volet</option>
									<option value="PAC">PAC</option>
									<option value="OPENING">Ouverture</option>
									<option value="FIX">Fixe</option>
								</select>
							</div>
						</div>

						<div class="form-group" id="flash">
							<label class="col-sm-2 control-label">{{Flash}}</label>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="flash" checked/></label>
							</div>
						</div>

						<div class="form-group" id="txt">
							<label class="col-sm-2 control-label">{{Texte avant les notifs}}</label>
							<div class="col-sm-3">
								<div class="input-group">
									<input type="text"  class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="txt" />
								</div>
							</div>
						</div>

						<div class="form-group" id="manage">
							<label class="col-sm-2 control-label">{{Gérer en local}}</label>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="manage" checked/></label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Texte à afficher}}</label>
							<div class="col-sm-3">
								<div class="input-group">
									<input type="text"  class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="addition" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher une commande" id="bt_selectMailCmd"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 50px;">#</th>
							<th style="width: 200px;">{{Nom}}</th>
							<th style="width: 200px;">{{Type}}</th>
							<th style="width: 100px;">{{Paramètres}}</th>
							<th style="width: 150px;"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'smartledmessenger', 'js', 'smartledmessenger');?>
<?php include_file('core', 'plugin.template', 'js');?>
