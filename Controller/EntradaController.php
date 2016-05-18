<?php

namespace MWSimple\Bundle\ForoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MWSimple\Bundle\AdminCrudBundle\Controller\DefaultController as Controller;
use MWSimple\Bundle\ForoBundle\Entity\Entrada;
use MWSimple\Bundle\ForoBundle\Form\EntradaType;
use MWSimple\Bundle\ForoBundle\Form\EntradaFilterType;

/**
 * Entrada controller.
 * @author Nombre Apellido <name@gmail.com>
 *
 * @Route("/foroentrada")
 */
class EntradaController extends Controller
{
    /**
     * Configuration file.
     */
    protected $config = array(
        'yml' => '/../Resources/config/Entrada.yml',
    );

    protected function getConfig(){
        $configs = Yaml::parse(file_get_contents(__DIR__ . $this->config['yml']));
        foreach ($configs as $key => $value) {
            $config[$key] = $value;
        }
        foreach ($this->config as $key => $value) {
            if ($key != 'yml') {
                $config[$key] = $value;
            }
        }
        return $config;
    }

    /**
     * Lists all Entrada entities.
     *
     * @Route("/", name="foro_entrada")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $this->config['filterType'] = new EntradaFilterType();
        $response = parent::indexAction();

        return $response;
    }

    /**
     * Creates a new Entrada entity.
     *
     * @Route("/", name="foro_entrada_create")
     * @Method("POST")
     * @Template("SistemaForoBundle:Entrada:new.html.twig")
     */
    public function createAction()
    {
        $config = $this->getConfig();
        $request = $this->getRequest();
        $entity = new $config['entity']();
        $form   = $this->createCreateForm($config, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();
            $entity->setAutor($user);
            $entity->setGrupo();
            
            $em->persist($entity);
            $em->flush();
            $this->useACL($entity, 'create');

            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            if (!array_key_exists('saveAndAdd', $config)) {
                $config['saveAndAdd'] = true;
            } elseif ($config['saveAndAdd'] != false) {
                $config['saveAndAdd'] = true;
            }

            if ($config['saveAndAdd']) {
                $nextAction = $form->get('saveAndAdd')->isClicked()
                ? $this->generateUrl($config['new'])
                : $this->generateUrl($config['show'], array('id' => $entity->getId()));
            } else {
                $nextAction = $this->generateUrl('foro_mws');
            }

            return $this->redirect($nextAction);
        }

        $this->get('session')->getFlashBag()->add('danger', 'flash.create.error');

        // remove the form to return to the view
        unset($config['newType']);

        return array(
            'config' => $config,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a entity.
    * @param array $config
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    protected function createCreateForm($config, $entity)
    {
        $form = $this->createForm($config['newType'], $entity, array(
            'action' => $this->generateUrl($config['create']),
            'method' => 'POST',
        ));

        $form
            ->add('idForo', 'hidden')
            ->add('save', 'submit', array(
                'translation_domain' => 'MWSimpleAdminCrudBundle',
                'label'              => 'publicar',
                'attr'               => array(
                    'class' => 'form-control btn-success',
                    'col'   => 'col-lg-2',
                )
            ))
        ;

        if (!array_key_exists('saveAndAdd', $config)) {
            $config['saveAndAdd'] = true;
        } elseif ($config['saveAndAdd'] != false) {
            $config['saveAndAdd'] = true;
        }

        if ($config['saveAndAdd']) {
            $form
                ->add('saveAndAdd', 'submit', array(
                    'translation_domain' => 'MWSimpleAdminCrudBundle',
                    'label'              => 'views.new.saveAndAdd',
                    'attr'               => array(
                        'class' => 'form-control btn-primary',
                        'col'   => 'col-lg-3',
                    )
                ))
            ;
        }

        return $form;
    }

    /**
     * Displays a form to create a new Entrada entity.
     *
     * @Route("/new", name="foro_entrada_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $this->config['newType'] = new EntradaType();
        $response = parent::newAction();

        return $response;
    }

    /**
     * Finds and displays a Entrada entity.
     *
     * @Route("/{id}", name="foro_entrada_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $response = parent::showAction($id);

        return $response;
    }

    /**
     * Displays a form to edit an existing Entrada entity.
     *
     * @Route("/{id}/edit", name="foro_entrada_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $this->config['editType'] = new EntradaType();
        $response = parent::editAction($id);

        return $response;
    }

    /**
     * Edits an existing Entrada entity.
     *
     * @Route("/{id}", name="foro_entrada_update")
     * @Method("PUT")
     * @Template("SistemaForoBundle:Entrada:edit.html.twig")
     */
    public function updateAction($id)
    {
        $this->config['editType'] = new EntradaType();
        $response = parent::updateAction($id);

        return $response;
    }

    /**
     * Deletes a Entrada entity.
     *
     * @Route("/{id}", name="foro_entrada_delete")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $response = parent::deleteAction($id);

        return $response;
    }

    /**
     * Exporter Entrada.
     *
     * @Route("/exporter/{format}", name="foro_entrada_export")
     */
    public function getExporter($format)
    {
        $response = parent::exportCsvAction($format);

        return $response;
    }

    // /**
    //  * Autocomplete a Entrada entity.
    //  *
    //  * @Route("/autocomplete-forms/get-autor", name="Entrada_autocomplete_autor")
    //  */
    // public function getAutocompleteUser()
    // {
    //     $options = array(
    //         'repository' => "SistemaForoBundle:User",
    //         'field'      => "id",
    //     );
    //     $response = parent::getAutocompleteFormsMwsAction($options);

    //     return $response;
    // }

    /**
     * Autocomplete a Entrada entity.
     *
     * @Route("/autocomplete-forms/get-grupo", name="Entrada_autocomplete_grupo")
     */
    public function getAutocompleteGrupo()
    {
        $options = array(
            'repository' => "MWSimpleForoBundle:Grupo",
            'field'      => "id",
        );
        $response = parent::getAutocompleteFormsMwsAction($options);

        return $response;
    }

    /**
     * Autocomplete a Entrada entity.
     *
     * @Route("/autocomplete-forms/get-respuestas", name="Entrada_autocomplete_respuestas")
     */
    public function getAutocompleteRespuesta()
    {
        $options = array(
            'repository' => "MWSimpleForoBundle:Respuesta",
            'field'      => "id",
        );
        $response = parent::getAutocompleteFormsMwsAction($options);

        return $response;
    }
}