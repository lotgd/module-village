# Village module
![Tests](https://github.com/lotgd/module-village/workflows/Tests/badge.svg)

A simple module which adds a village square scene to the game upon installation.
It uses the default-scene hook to relay new characters to the village square.

## Scene templates

`LotGD\Module\Village\SceneTemplates\VillageScene`
- The module uses this scene template to forward the character to the default scene. 
  If it does not exist, it will log an error and the character will be stuck.
  If there are multiple village scenes available, it chooses the first it finds.

## Event subscriptions
- `#h/lotgd/core/default-scene#`
