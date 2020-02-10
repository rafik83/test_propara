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


class MuDatatable extends AbstractDatatableView
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
            "url" => $this->router->generate("mu_results"),
            "type" => "GET"
        ));

        $this->options->set(array(
            "display_start" => 0,
            "dom" => "lfrtip",
            "length_menu" => array(10, 25, 50, 100),
            "order_classes" => true,
            "order" => [[0, "desc"]],
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
            ->add("createdAt", "datetime", array("title" => "Date",'date_format' => 'DD/MM/YYYY HH:mm:ss',))
            ->add("month", "column", array("title" => "Mois",))
            ->add("year", "column", array("title" => "Année",))
            ->add("company.nom", "column", array("title" => "Entreprise",))
            ->add("nbBulletins", "column", array("title" => "Nombre de bulletins téléchargés",))
            ->add('signed', 'boolean', array(
                'title' => 'Traitement',
                'true_icon' => 'fa fa-certificate text-system',
                'false_icon' => 'glyphicon glyphicon-remove',
                'true_label' => 'Signé',
                'false_label' => 'En attente',
                'class' => 'green'
            ))
            ->add("commentError", "column", array("title" => "Erreur d'execution",))
            ->add(null, "action", array(
                "title" => "Actions",
                "start_html" => '<div class="wrapper" style="text-align:center">',
                "end_html" => '</div>',
                "actions" => array(
                    array(
                        "route" => "display_logs_mu",
                        "route_parameters" => array(
                            "id" => "id"
                        ),
                        "icon" => "fa fa-search",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Voir les logs",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "role" => "ROLE_ADMIN",
                    ),

                )
            ));

    }
    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return "BackBundle\Entity\MassUpload";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "mass_upload_form";
    }

}
