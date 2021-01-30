<?php
declare(strict_types=1);

namespace LotGD\Module\Village\SceneTemplates;

use LotGD\Core\Models\Scene;
use LotGD\Core\Models\SceneConnectionGroup;
use LotGD\Core\Models\SceneTemplate;
use LotGD\Core\SceneTemplates\SceneTemplateInterface;
use LotGD\Module\Village\Module;

class VillageScene implements SceneTemplateInterface
{
    const Groups = [
        "lotgd/module-village/outside",
        "lotgd/module-village/residential",
        "lotgd/module-village/marketsquare"
    ];

    public static function getNavigationEvent(): string
    {
        return Module::VillageScene;
    }

    public static function getScaffold(): Scene
    {
        $template = new SceneTemplate(self::class, Module::Module);

        $scene = new Scene(
            title: "Village Square",
            description: "The village square hustles and bustles. No one really notices "
                ."that you're are standing there. You see various shops and businesses along "
                ."main street. There is a curious looking rock to one side. On every side the "
                ."village is surrounded by deep dark forest.",
            template: $template,
        );

        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[0], "Outside"));
        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[1], "Residential District"));
        $scene->addConnectionGroup(new SceneConnectionGroup(self::Groups[2], "The Marketsquare"));

        return $scene;
    }
}