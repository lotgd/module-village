<?php
declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;
use LotGD\Core\Models\Module as ModuleModel;
use LotGD\Core\Models\Scene;
use LotGD\Core\Models\SceneTemplate;
use LotGD\Module\Village\Module;
use LotGD\Module\Village\SceneTemplates\VillageScene;

class sceneTemplateRemovalTest extends ModuleTest
{
    public function getDataSet(): array
    {
        return Yaml::parseFile(implode(DIRECTORY_SEPARATOR, [__DIR__, 'datasets', 'sceneTemplateRemoval.yml']));
    }

    public function testIfSceneTemplateGetsTurnedToNullIfModuleWasRemoved()
    {
        $em = $this->getEntityManager();

        // Get SceneTemplate
        $sceneTemplate = $em->getRepository(SceneTemplate::class)->find(VillageScene::class);

        // Get Scene
        $scene = $em->getRepository(Scene::class)->find("20000000-0000-0000-0000-000000000001");

        // Change scene template and save
        $scene->setTemplate($sceneTemplate);
        $em->flush();
        $em->clear();


        Module::onUnregister($this->g, $this->moduleModel);
        $m = $this->getEntityManager()->getRepository(ModuleModel::class)->find(self::Library);
        $m->delete($this->getEntityManager());

        // flush changes
        $this->getEntityManager()->flush();

        $this->assertDataWasKeptIntact();

        // Since tearDown() contains an onUnregister() call, this also tests
        // double-unregistering, which should be properly supported by modules.
    }
}