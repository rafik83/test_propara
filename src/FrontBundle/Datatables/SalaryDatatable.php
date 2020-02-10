<?php

namespace FrontBundle\Datatables;

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

class SalaryDatatable extends AbstractDatatableView {

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
            "url" => $this->router->generate("front_salary_result"),
            "type" => "GET"
        ));

        $this->options->set(array(
            "display_start" => 0,
            "dom" => "lfrtip",
//            "length_menu" => array(10, 25, 50, 100, 500, 1000),
//            "length_menu" => array(50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000),
             "length_menu" => array(100, 150, 200, 250, 300, 350, 400, 450, 500, 1000),
            "order_classes" => true,
            "order" => [[0, "asc"]],
            "order_multi" => true,
//            "page_length" => 10,
            "page_length" => 100,
            "paging_type" => Style::FULL_NUMBERS_PAGINATION, //BOOTSTRAP_3_STYLE , FULL_NUMBERS_PAGINATION
            "renderer" => "",
            "scroll_collapse" => false,
            "search_delay" => 0,
            "state_duration" => 7200,
            "stripe_classes" => array(),
            "class" => Style::BOOTSTRAP_3_STYLE,
            "individual_filtering" => true //dataTables_scrollFoot
            //"individual_filtering_position" => "head", //both foot,head
//            "use_integration_options" => false,
            //"use_integration_options" => true,
            //'force_dom' => true
        ));

        $this->columnBuilder
                ->add(null, 'multiselect', array(
                    'start_html' => '<div class="wrapper" id="testwrapper">',
                    'end_html' => '</div>',
                    'attributes' => array(
                        'class' => 'testclass',
                        'name' => 'testname',
                    ),
                    'actions' => array(
                        array(
                            'route' => 'send_activation',
                            'label' => 'Envoyer une demande d\'activation',
//                            'role' => 'ROLE_RH_LIMITE',
//                            'role' => 'ROLE_RH',
                            'icon' => 'fa fa-unlock',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'Envoyer un email d\'activation',
                                'class' => 'btn btn-xs btn-primary custom-btn',
                                'role' => 'button'
                            ),
                        )
                    )
                ))
                ->add("nom", "column", array("title" => "Nom",))//emailPerso
                ->add("prenom", "column", array("title" => "Prénom",))
                ->add("emailPerso", "column", array("title" => "E-mail Personnelle",))
//            ->add("dateBegin", "datetime", array("title" => "Date d'entrée",'date_format' => 'DD/MM/YYYY',))
                ->add("dateEnd", "datetime", array("title" => "Date de sortie", 'date_format' => 'DD/MM/YYYY',))
                ->add("matricule", "column", array("title" => "Matricule",))
                ->add("natureContrat", "column", array("title" => "Contrat",))
                ->add("company.nom", "column", array("title" => "Entreprise",))
                ->add("isDoc", 'boolean', array(
                    'title' => 'Documents',
                    'true_icon' => 'fa fa-inbox text-system',
                    'false_icon' => 'glyphicon glyphicon-remove text-danger',
                    'true_label' => 'Oui',
                    'false_label' => 'Non'
                ))
                ->add("user.isActive", 'boolean', array(
                    'title' => 'Statut ',
                    'true_icon' => 'fa fa-check text-system',
                    'false_icon' => 'fa fa-user-times text-danger',
                    'true_label' => 'Activé',
                    'false_label' => 'Désactivé'
                ))
                ->add("activationSended", 'boolean', array(
                    'title' => '',
                    'true_icon' => 'fa fa-envelope text-system',
                    'false_icon' => 'fa fa-exclamation-circle text-danger',
                ))
                ->add(null, "action", array(
                    "title" => "Actions",
                    "start_html" => '<div class="wrapper" style="text-align:center">',
                    "end_html" => '</div>',
                    "actions" => array(
                        array(
                            "route" => "front_salary_edit",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "glyphicon glyphicon-edit",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Modifier la fiche",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
//                            "role"=> "ROLE_RH_LIMITE",
//                            "role"=> "ROLE_RH",
                        ),
                        array(
                            "route" => "front_salary_disable",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "fa fa-user-times",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Désactiver la fiche",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
                            "confirm" => true,
                            "confirm_message" => "La fiche de ce salarié va être désactivée ",
//                            "role" => "ROLE_RH_LIMITE",
//                            "role"=> "ROLE_RH",
                            "render_if" => array(
                                "user.isActive" => true
                            )
                        ),
                        array(
                            "route" => "front_salary_enable",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "fa fa-user-plus",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Activer la fiche",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
                            "confirm" => true,
                            "confirm_message" => "La fiche de ce salarié va être activé ",
//                            "role" => "ROLE_RH_LIMITE",
//                            "role"=> "ROLE_RH",
                            "render_if" => array(
                                "user.isActive" => false
                            )
                        ),
                        array(
                            "route" => "front_profile_salary",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "fa fa-user",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Consulter la fiche",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
//                            "role" => "ROLE_RH_LIMITE",
//                            "role"=> "ROLE_RH",
                        ),
                        array(
                            "route" => "front_salary_docs",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "fa fa-files-o",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Gestion des documents",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
//                            "role" => "ROLE_RH_LIMITE",
//                            "role"=> "ROLE_RH",
                        )
                    )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity() {
        return "FrontBundle\Entity\Salary";
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return "salary_result";
    }

}
