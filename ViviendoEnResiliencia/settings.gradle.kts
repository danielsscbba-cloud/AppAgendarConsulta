// settings.gradle.kts

pluginManagement {
    repositories {
        google() // Repositorio de Google para plugins de Android. [1]
        mavenCentral() // Repositorio central de Maven. [1]
        gradlePluginPortal() // Portal de plugins de Gradle. [1]
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google() // Repositorio de Google para dependencias. [1]
        mavenCentral() // Repositorio central de Maven para dependencias. [1]
    }
}

rootProject.name = "ViviendoEnResiliencia"
include(":app")