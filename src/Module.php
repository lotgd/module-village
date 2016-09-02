<?php
declare(strict_types=1);

namespace LotGD\Module\Village;

use LotGD\Core\Game;
use LotGD\Core\Module as ModuleInterface;
use LotGD\Core\Models\Module as ModuleModel;
use LotGD\Core\Models\Scene;

class Module implements ModuleInterface {
    const Module = "lotgd/module-village";
    const VillageScene = "lotgd/module-village/village";
    const VillageSceneArrayProperty = "lotgd/module-village/scenes";

    public static function handleEvent(Game $g, string $event, array &$context)
    {
        switch ($event) {
            case 'h/lotgd/core/default-scene':
                $character = $context["character"];

                if (is_null($context["scene"])) {
                    // search village scene
                    // ToDo: Maybe use a dynamic setting and let the default scene configurable by daenerys?
                    $villageScene = $g->getEntityManager()->getRepository(Scene::class)
                        ->findOneBy(["template" => self::VillageScene]);

                    if ($villageScene !== null) {
                        $context["scene"] = $villageScene;
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
                break;
        }
    }

    private static function getBaseScene(): Scene
    {
        return Scene::create([
            'template' => self::VillageScene,
            'title' => 'Village Square',
            'description' => "This is the village square."
        ]);
    }

    private static function storeSceneId(ModuleModel $module, int $id)
    {
        $module->setProperty(self::VillageSceneArrayProperty, $id);
    }

    public static function onRegister(Game $g, ModuleModel $module)
    {
        // Add a single village scene which can be used as an anchor.
        $g->getLogger()->addNotice(sprintf(
            "%s: Adds a basic village scene.",
            self::Module
        ));

        $village = self::getBaseScene();
        $village->save($g->getEntityManager());

        self::storeSceneId($module, $village->getId());
    }

    public static function onUnregister(Game $g, ModuleModel $module)
    {
        $sceneId = $module->getProperty(self::VillageSceneArrayProperty);
        if ($sceneId !== null) {
            $scene = $g->getEntityManager()->getRepository(Scene::class)
                ->find($sceneId);
            $g->getEntityManager()->remove($scene);
            $module->setProperty(self::VillageSceneArrayProperty, null);
        }
    }
}
