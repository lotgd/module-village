<?php
declare(strict_types=1);

namespace LotGD\Module\Village;

use Doctrine\ORM\EntityManagerInterface;
use LotGD\Core\Events\EventContext;
use LotGD\Core\Game;
use LotGD\Core\Models\SceneConnectionGroup;
use LotGD\Core\Module as ModuleInterface;
use LotGD\Core\Models\Module as ModuleModel;
use LotGD\Core\Models\Scene;
use LotGD\Core\Models\SceneTemplate;
use LotGD\Module\Village\SceneTemplates\VillageScene;

const MODULE = "lotgd/module-village";

class Module implements ModuleInterface {
    const Module = MODULE;
    const VillageScene = MODULE . "/village";
    const AutomaticallyRegisteredScenes = MODULE . "/scenes";
    const Groups = [
        "lotgd/module-village/outside",
        "lotgd/module-village/residential",
        "lotgd/module-village/marketsquare"
    ];

    public static function handleEvent(Game $g, EventContext $context): EventContext
    {
        if ($context->getEvent() === "h/lotgd/core/default-scene") {
            $context = self::handleDefaultScene($g, $context);
        }

        return $context;
    }

    private static function handleDefaultScene(Game $g, EventContext $context): EventContext
    {
        if ($context->getDataField("scene") === null) {
            // search village scene
            // ToDo: Maybe use a dynamic setting and let the default scene configurable by daenerys?
            $villageScene = $g->getEntityManager()->getRepository(Scene::class)
                ->findOneBy(["template" => VillageScene::class]);

            if ($villageScene !== null) {
                // Set scene on EventContext to the found village scene.
                $context->setDataField("scene", $villageScene);
            }
            else {
                $g->getLogger()->notice(sprintf(
                    "%s: Tried to find default scene, but none with %s as a template has been found",
                    self::Module,
                    self::VillageScene
                ));
            }
        }
        else {
            $g->getLogger()->notice(sprintf(
                "%s: Called for hook /h/lotgd/core/default-scene, but scene in context is not null.",
                self::Module
            ));
        }

        return $context;
    }

    private static function getBaseScene(): Scene
    {
        $scene = Scene::create([
            'template' => new SceneTemplate(VillageScene::class, MODULE),
            'title' => 'Village Square',
            'description' => "The village square hustles and bustles. No one really notices "
                ."that you're are standing there. You see various shops and businesses along "
                ."main street. There is a curious looking rock to one side. On every side the "
                ."village is surrounded by deep dark forest."
        ]);

        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[0], "Outside"));
        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[1], "Residential District"));
        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[2], "The Marketsquare"));

        return $scene;
    }

    public static function onRegister(Game $g, ModuleModel $module)
    {
        /** @var EntityManagerInterface $em */
        $em = $g->getEntityManager();

        // Add a single village scene which can be used as an anchor.
        $g->getLogger()->notice(sprintf(
            "%s: Adds a basic village scene.",
            self::Module
        ));

        // Register scene

        $village = self::getBaseScene();

        $em->persist($village);
        $em->persist($village->getTemplate());

        $module->setProperty(self::AutomaticallyRegisteredScenes, $village->getId());

        // no flush
    }

    /**
     *
     * @param Game $g
     * @param ModuleModel $module
     */
    public static function onUnregister(Game $g, ModuleModel $module)
    {
        /** @var EntityManagerInterface $em */
        $em = $g->getEntityManager();

        // Get automatically registered village scene
        $sceneId = $module->getProperty(self::AutomaticallyRegisteredScenes);

        // Get all village scenes with this template.
        $scenes = $em->getRepository(Scene::class)->findBy(["template" => VillageScene::class]);

        foreach ($scenes as $scene) {
            // Remove template
            $em->remove($scene->getTemplate());

            if ($scene->getId() === $sceneId) {
                // Remove automatically registered scene
                $em->remove($scene);
                $module->setProperty(self::AutomaticallyRegisteredScenes, null);

                $g->getLogger()->notice(sprintf(
                    "%s: Removed automatically registered scene (%s, id=%s).",
                    self::Module, $scene->getTitle(), $scene->getId()
                ));
            } else {
                // Set other scenes to have a null-template.
                $scene->setTemplate(null);

                $g->getLogger()->notice(sprintf(
                    "%s: Set template of scene using this modules templates to null (%s, id=%s).",
                    self::Module, $scene->getTitle(), $scene->getId()
                ));
            }
        }
    }
}
