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

class ResponsableDatatable extends AbstractDatatableView {

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
            "url" => $this->router->generate("users_company_result"),
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
                
//                ->add("companies.nom", "array", array("title" => "Nom",
//                    'data' => 'companies[, ].nom',
//                ))
                ->add("libeller", "column", array("title" => "Libeller",))
                ->add("createdAt", "datetime", array("title" => "Date création", 'date_format' => 'DD/MM/YYYY',))
//                ->add("users.", "column", array("title" => "Utilisateur",))
//                ->add("users.username", "column", array("title" => "Utilisateur",))
//                ->add("users.roles.name", "array", array("title" => "Rôles",
//                    'data' => 'users.roles[, ].name',
//                ))
                ->add(null, "action", array(
                    "title" => "Actions",
                    "start_html" => '<div class="wrapper" style="text-align:center">',
                    "end_html" => '</div>',
                    "actions" => array(
                        array(
                            "route" => "users_company_edit",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "glyphicon glyphicon-edit",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Edit",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
//                            "role" => "ROLE_ADMIN",
                        ),
                        array(
                            "route" => "users_company_remove",
                            "route_parameters" => array(
                                "id" => "id"
                            ),
                            "icon" => "glyphicon glyphicon-remove",
                            "attributes" => array(
                                "rel" => "tooltip",
                                "title" => "Edit",
                                "class" => "btn btn-xs btn-primary custom-btn",
                                "role" => "button"
                            ),
                            "confirm" => true,
                            "confirm_message" => "Voulez vous détruire cette donnée ?",
//                            "role" => "ROLE_ADMIN",
                        )
                    )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity() {
        return "FrontBundle\Entity\Responsable";
//        return "FrontBundle\Entity\User";
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
//        return "users_company_result";
        return "responsable_company_result";
    }

}
