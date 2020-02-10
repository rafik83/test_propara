<?php

namespace BackBundle\Datatables;

/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;

class BuDatatable extends AbstractDatatableView {

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter() {
        $formatter = function($line) {
            switch ($line['commentError']) {
                case 'En attente' :
                    $line['progressTag'] = "<i class='fa fa-clock-o'></i> En attente";
                    break;
                case 'En cours' :
                    $line['progressTag'] = "<i class='fa fa-spinner'></i> En cours";
                    break;
                case 'Terminé' :
                    $line['progressTag'] = "<i class='fa fa-check'></i> Terminé";
                    break;
                case 'Signé' :
                    $line['progressTag'] = "<i class='fa fa-certificate text-system'></i> Signé";
                    break;
                case 'Erreurs lors de l execution' :
                    $line['progressTag'] = "<i class='fa fa-times'></i> Erreurs lors de l execution";
                    break;
            }

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable(array $options = array()) {

        $this->features->set(array(
            "auto_width" => true,
            "defer_render" => false,
            "info" => true,
            "jquery_ui" => false,
            "length_change" => true,
            "ordering" => true,
            "paging" => true,
            "processing" => true,
            "scroll_x" => false,
            "scroll_y" => "",
            "searching" => true,
            "server_side" => true,
            "state_save" => false,
            "delay" => 0
        ));

        $this->ajax->set(array(
            "url" => $this->router->generate("bu_result"),
            "type" => "GET"
        ));

        $this->options->set(array(
            "display_start" => 0,
            "dom" => "lfrtip",
            "length_menu" => array(10, 25, 50, 100),
            "order_classes" => true,
            "order" => [[0, "asc"]],
            "order_multi" => true,
            "page_length" => 10,
            "paging_type" => Style::FULL_NUMBERS_PAGINATION,
            "renderer" => "",
            "scroll_collapse" => false,
            "search_delay" => 0,
            "state_duration" => 7200,
            "stripe_classes" => array(),
            "class" => Style::BOOTSTRAP_3_STYLE,
            "individual_filtering" => false,
            "individual_filtering_position" => "foot",
            "use_integration_options" => false
        ));

        $this->columnBuilder
                ->add("id", "column", array("title" => "Id",))
                ->add("month", "column", array("title" => "Mois",))
                ->add("year", "column", array("title" => "Année",))
                ->add("salary.matricule", "column", array("title" => "Matricule",))
                ->add("createdAt", "datetime", array("title" => "Date", 'date_format' => 'DD/MM/YYYY',))
//                ->add('signed', 'boolean', array(
//                    'title' => 'Traitement',
//                    'true_icon' => 'fa fa-certificate text-system',
//                    'false_icon' => 'glyphicon glyphicon-remove',
//                    'true_label' => 'Signé',
//                    'false_label' => 'En attente',
//                    'class' => 'green'
//                ))
//                ->add('signed', 'virtual', array("title" => "Traitement"))
                ->add('progressTag', 'virtual', array("title" => "Traitement"))
                ->add('commentError', 'column', array('visible' => false))
                ->add('disabled', 'boolean', array(
                    'title' => 'Activation',
                    'true_icon' => 'fa fa-minus-circle',
                    'false_icon' => 'fa fa-cog  text-system',
                    'true_label' => 'Désactivé',
                    'false_label' => 'Activé',
                    'class' => 'green'
                ))
//                ->add("commentError", "column", array("title" => "Erreur d'execution",))
                ->add(null, "action", array(
                    "title" => "Actions",
                    "start_html" => '<div class="wrapper" style="text-align:center">',
                    "end_html" => '</div>',
                    "actions" => array(
                        array(
                            "route" => "disable_bu",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "fa fa-minus-circle",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Désactiver le bulletin",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
                            "role" => "ROLE_ADMIN",
                            "confirm" => true,
                            "confirm_message" => "Voulez-vous vraiment désactiver le bulletin ?",
                            "render_if" => array(
                                "disabled" => false
                            )
                        ),
                        array(
                            "route" => "front_profile_salary",
                            "route_parameters" => array(
                                "id" => "salary.id"
                            ),
                            "icon" => "fa fa-user",
                            "label" => "Profile",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Profile du salarié",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
                            "role" => "ROLE_ADMIN"
                        ),
                    )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity() {
        return "BackBundle\Entity\Bu";
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return "bu_datatable";
    }

}
