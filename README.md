# Phing Tasks

This project contains Smush.It task to [Phing](http://phing.info) build tool for PHP.

## Smush.It Task

Defines a Phing task to run the [Smush.it](http://www.smushit.com/ysmush.it/) compressor against a set of image files.

To setup, put `SmushitCompressorTask.php` under Phing `tasks/ext`:

To use this task, include it with a taskdef tag in your build.xml file:

    <taskdef name="smushit" classname="ext.tasks.SmushitCompressorTask" />

The task is now ready to be used:

    <target name="smushit" description="Optimize with Smush.it">
        <smushit targetdir="path/to/target">
            <fileset dir="path/to/source">
                <include name="**/*.jpg" />
                <include name="**/*.jpeg" />
                <include name="**/*.gif" />
                <include name="**/*.png" />
            </fileset>
        </smushit>
    </target>

This task makes use of [smushit](https://github.com/davgothic/SmushIt) by
GitHub user [davgothic](https://github.com/davgothic), make sure it is in php `include_path`.

### Task Attributes

#### Required
 - **targetdir** - Specifies the directory path for output files.

#### Optional
_There are no optional attributes for this task._


### TODO

 - add option to skip saving filetype changes
