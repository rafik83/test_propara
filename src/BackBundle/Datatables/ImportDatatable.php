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


class ImportDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        $formatter = function($line){
            switch($line['progress'] ) {
                case 0 :
                    $line['progressTag'] = "<i class='fa fa-clock-o'></i> En attente";
                    break;
                case 1 :
                    $line['progressTag'] = "<i class='fa fa-spinner'></i> En cours";
                    break;
                case 2 :
                    $line['progressTag'] = "<i class='fa fa-check'></i> Terminé";
                    break;
                case 99 :
                    $line['progressTag'] = "<i class='fa fa-times'></i> Erreurs lors de l'execution";
                    break;
            }

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
            "url" => $this->router->generate("import_salaries_result"),
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
            ->add("createdAt", "datetime", array("title" => "Date",'date_format' => 'DD/MM/YYYY',))
            ->add('progressTag', 'virtual', array("title" => "Avancement de l'importation"))
            ->add('progress', 'column', array('visible' => false))
            ->add("commentError", "column", array("title" => "Erreur d'execution",))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return "BackBundle\Entity\Import";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "import_datatable";
    }

}
