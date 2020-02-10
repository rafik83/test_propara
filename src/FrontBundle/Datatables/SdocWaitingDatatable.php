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


class SdocWaitingDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function($line){
            $line['completeName'] = $line['salary']['nom'] . ', ' . $line['salary']['prenom'];

            return $line;
        };

        return $formatter;
    }

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
            "url" => $this->router->generate("front_sdoc_waiting_result"),
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
            ->add(null, 'multiselect', array(
                'start_html' => '<div class="wrapper" id="testwrapper">',
                'end_html' => '</div>',
                'attributes' => array(
                    'class' => 'testclass',
                    'name' => 'testname',
                ),
                'actions' => array(
                    array(
                        'route' => 'send_relance',
                        'label' => 'Envoyer une relance',
                        'role' => 'ROLE_RH',
                        'icon' => 'fa fa-send',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Envoyer une relance',
                            'class' => 'btn btn-xs btn-primary custom-btn',
                            'role' => 'button'
                        ),
                    )
                )
            ))
            ->add('salary.nom', 'column', array('visible' => false))
            ->add('salary.prenom', 'column', array('visible' => false))
            ->add('salary.id', 'column', array('visible' => false))
            ->add("name", "column", array("title" => "Nom du fichier",))
            ->add("createdAt", "datetime", array("title" => "Date",'date_format' => 'DD/MM/YYYY',))
            ->add("completeName", "virtual", array("title" => "Nom complet du salarié",))
            ->add(null, "action", array(
                "title" => "Actions",
                "start_html" => '<div class="wrapper" style="text-align:center">',
                "end_html" => '</div>',
                "actions" => array(
                    array(
                        "route" => "front_salary_edit",
                        "route_parameters" => array(
                            "id" => "salary.id"
                        ),
                        "icon" => "glyphicon glyphicon-edit",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Modifier la fiche",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "role" => "ROLE_RH",
                    ),

                    array(
                        "route" => "front_profile_salary",
                        "route_parameters" => array(
                            "id" => "salary.id"
                        ),
                        "icon" => "fa fa-user",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Consulter la fiche",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
                        "role" => "ROLE_RH",
                    ),
                    array(
                        "route" => "front_salary_docs",
                        "route_parameters" => array(
                            "id" => "salary.id"
                        ),
                        "icon" => "fa fa-files-o",
                        "attributes" => array(
                            "rel" => "tooltip",
                            "title" => "Gestion des documents",
                            "class" => "btn btn-xs btn-primary custom-btn",
                            "role" => "button"
                        ),
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
        return "FrontBundle\Entity\Document";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "sdoc_waiting_result";
    }

}
