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


class CompanyDatatable extends AbstractDatatableView
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
            "url" => $this->router->generate("front_company_result"),
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
            ->add("nom", "column", array("title" => "Nom de l'entreprise",))
            ->add(null, "action", array(
                "title" => "Actions",
                "start_html" => '<div class="wrapper" style="text-align:center">',
                "end_html" => '</div>',
                "actions" => array(
                    array(
                        "route" => "front_company_edit",
                        "route_parameters" => array(
                            "id" => "id"
                        ),
                        "icon" => "glyphicon glyphicon-edit",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Modifier la catégorie",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "role" => "ROLE_RH",
                    ),
                    array(
                        "route" => "front_company_remove",
                        "route_parameters" => array(
                            "id" => "id"
                        ),
                        "icon" => "glyphicon glyphicon-remove",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Supprimer la catégorie",
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
        return "FrontBundle\Entity\Company";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "company_result";
    }

}
