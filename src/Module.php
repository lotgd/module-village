<?php
declare(strict_types=1);

namespace LotGD\Module\Village;

use LotGD\Core\Events\EventContext;
use LotGD\Core\Game;
use LotGD\Core\Models\SceneConnectionGroup;
use LotGD\Core\Module as ModuleInterface;
use LotGD\Core\Models\Module as ModuleModel;
use LotGD\Core\Models\Scene;

class Module implements ModuleInterface {
    const Module = "lotgd/module-village";
    const VillageScene = "lotgd/module-village/village";
    const VillageSceneArrayProperty = "lotgd/module-village/scenes";
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
                ->findOneBy(["template" => self::VillageScene]);

            if ($villageScene !== null) {
                $context->setDataField("scene", $villageScene);
            }
            else {
                $g->getLogger()->addNotice(sprintf(
                    "%s: Tried to find default scene, but none with %s as a template has been found",
                    self::Module,
                    self::VillageScene
                ));
            }
        }
        else {
            $g->getLogger()->addNotice(sprintf(
                "%s: Called for hook /h/lotgd/core/default-scene, but scene in context is not null.",
                self::Module
            ));
        }

        return $context;
    }

    private static function getBaseScene(): Scene
    {
        $scene = Scene::create([
            'template' => self::VillageScene,
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
        $sceneId = $module->getProperty(self::VillageSceneArrayProperty);

        if ($sceneId === null) {
            // Add a single village scene which can be used as an anchor.
            $g->getLogger()->addNotice(sprintf(
                "%s: Adds a basic village scene.",
                self::Module
            ));

            $village = self::getBaseScene();
            $village->save($g->getEntityManager());

            $module->setProperty(self::VillageSceneArrayProperty, $village->getId());
        }
    }

    public static function onUnregister(Game $g, ModuleModel $module)
    {
        $sceneId = $module->getProperty(self::VillageSceneArrayProperty);

        if ($sceneId !== null) {
            /** @var Scene $scene */
            $scene = $g->getEntityManager()->getRepository(Scene::class)
                ->find($sceneId);
            $scene->delete($g->getEntityManager());
            $module->setProperty(self::VillageSceneArrayProperty, null);
        }
    }
}
