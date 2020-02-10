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


class CoDocDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable(array $options = array())
    {

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
            "url" => $this->router->generate("front_codoc_result"),
            "type" => "GET"
        ));

        $this->options->set(array(
            "display_start" => 0,
            "dom" => "lfrtip",
            "length_menu" => array(10, 25, 50, 100),
//            "length_menu" => array(50, 100,150,200),
            "order_classes" => true,
            "order" => [[0, "asc"]],
            "order_multi" => true,
            "page_length" => 10,
//            "page_length" => 50,
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
            ->add("name", "column", array("title" => "Nom du document",))
            ->add("companies.nom", "array", array("title" => "Entités",
                'data' => 'companies[, ].nom',
            ))
            ->add("createdAt", "datetime", array("title" => "Date",'date_format' => 'DD/MM/YYYY',))
            ->add(null, "action", array(
                "title" => "Actions",
                "start_html" => '<div class="wrapper" style="text-align:center">',
                "end_html" => '</div>',
                "actions" => array(

                    array(
                        "route" => "front_codoc_download",
                        "route_parameters" => array(
                            "id" => "id"
                        ),
                        "icon" => "fa fa-download",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Télécharger le document",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "confirm" => false,
                        "role" => "ROLE_RH",
                    ),
                    array(
                        "route" => "front_codoc_remove",
                        "route_parameters" => array(
                            "id" => "id"
                        ),
                        "icon" => "glyphicon glyphicon-remove",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Supprimer le document",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "confirm" => true,
                        "confirm_message" => "Voulez vous détruire cette donnée?",
                        "role" => "ROLE_RH",
                    )
                )
            ));

    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return "FrontBundle\Entity\CoDoc";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "co_doc_result";
    }

}
