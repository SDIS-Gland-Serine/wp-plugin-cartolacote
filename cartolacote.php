<?php
/*
Plugin Name: CartoLaCôte
Description: -
Version: 1.0
Author: Vincent Barbay
Author URI: http://
Plugin URI: http://
*/

define('CARTO_DIR', plugin_dir_path(__FILE__));
define('CARTO_URL', plugin_dir_url(__FILE__));

function carto_load(){
#	require_once(CARTO_DIR.'includes/getDatas.php');
}

function carto_activation() {
	//actions to perform once on plugin activation go here    
	
	//register uninstaller
	register_uninstall_hook(__FILE__, 'carto_uninstall');
}

function carto_deactivation() {
	// actions to perform once on plugin deactivation go here
	
}

function carto_uninstall(){
	//actions to perform once on plugin uninstall go here
	
}


function carto_admin_menu() {

}



// RUN

carto_load();

register_activation_hook(__FILE__, 'carto_activation');
register_deactivation_hook(__FILE__, 'carto_deactivation');

add_action( 'admin_menu', 'carto_admin_menu' );



/**
 * Enqueue stylesheet and script
 */
function enqueue_carto() {
	wp_enqueue_style( 'carto-css', plugins_url( 'cartolacote/assets/css/carto.css' ) );
#	wp_enqueue_script(
#		'carto-js', 
#		plugins_url( 'gestion-droits/assets/js/gestion-carto.js' ), 
#		array(),
#		time(),
#		true
#	);
}
add_action( 'admin_init', 'enqueue_carto' );


add_action( 'wp_dashboard_setup', 'carto_dashboard_add_widgets' );
function carto_dashboard_add_widgets() {
	wp_add_dashboard_widget( 'carto_dashboard_widget_map', __( 'Chantiers et perturbations de trafic', 'cartolacote' ), 'carto_dashboard_widget_map_handler' );
}

function carto_dashboard_widget_map_handler() {
	echo '<iframe width="390" height="550" src="https://map.cartolacote.ch/iframe/theme/chantiers_perturbations_trafic?lang=fr&tree_group_layers_points_interet=&baselayer_ref=plan_ville_gris&baselayer_opacity=0&theme=chantiers_perturbations_trafic&tree_group_layers_mobilier_panneaux=&map_x=2509365&map_y=1142710&map_zoom=1&tree_group_layers_intercommunalites=car_partenaire_sdis_gs&tree_groups=intercommunalites%2Cchantiers_perturbations_trafic_public&tree_group_opacity_intercommunalites=0.5" frameborder="0" style="border:0"></iframe>';

	$chantiers = get_transient("carto_chantiers"); 
	if( !$chantiers ) {  
		$json = wp_remote_retrieve_body( wp_remote_get('https://map.cartolacote.ch/webservice/chantiers?entite=sdis_gs', array('sslverify' => false)) );
		$chantiers = json_decode($json, true)['chantiers'];		
		set_transient('carto_chantiers', $chantiers, 60 * 60); // 60 minutes
	}

	echo "<!-- ";
	print_r($chantiers);
	echo " -->";
	echo "<ul id='listeChantiers'>";
		foreach($chantiers as $ch){
			if($ch['confirme'] == "Oui"){
				echo "<li>";
					echo "<b>";
						echo $ch['evenement'];
					echo "</b>";
					echo "<br />";
					echo "<small>";
						echo $ch['service_pilote'];
						if($ch['telephone_contact'] != null) echo " <a href='tel:".$ch['telephone_contact']."'><span class='dashicons dashicons-phone'></span></a>";
						if($ch['email_contact'] != null) echo " <a href='mailto:".$ch['email_contact']."'><span class='dashicons dashicons-email-alt'></span></a>";
					echo "</small>";
					echo "<ul>";
						foreach($ch['perturbations'] as $pe){
							switch($pe['etat_secours']){
								case 'Passé' : $class='past'; break;
								case 'En cours' : $class='running'; break;
								case 'Futur' : $class='future'; break;
							}
							echo "<li class='".$class."'>";
								echo $pe['type_perturbation'];
								echo "<br />";
								echo "<small>";
									echo $pe['periode_secours'];
								echo "</small>";
							echo "</li>";
								
								/*
periode_public : période pendant laquelle a lieu la perturbation
date_debut_public : date de début des travaux
date_fin_public : date de fin des travaux
etat_public : état de la perturbation (Passé, En cours, Futur)

periode_secours : période pendant laquelle a lieu la perturbation (uniquement pour les secours)
date_debut_secours : Date de début des travaux (uniquement pour les secours)
date_fin_secours : Date de fin des travaux (uniquement pour les secours)
etat_secours : état de la perturbation (Passé, En cours, Futur) (uniquement pour les secours)								
								
								*/
						}
					echo "</ul>";
				echo "</li>";
			}			
		
			
		}
	
	echo "</ul>";


}

