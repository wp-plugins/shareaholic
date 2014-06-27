# Copyright Shareaholic, Inc. (www.shareaholic.com).  All Rights Reserved.

desc 'Get plugin ready for WordPress directory'
task :makerelease do
    Rake::Task['min'].invoke
end

desc 'Minify scripts using closure compiler'
task :min => "compiler/compiler.jar" do
  files = %w(main)
  js_root = File.expand_path(File.join(File.dirname(__FILE__),'assets/js'))
  files.each do |name|
    src = File.join(js_root,"#{name}.js")
    dest = File.join(js_root,"#{name}.min.js")
    cmd = "java -jar compiler/compiler.jar "
    cmd << "--js #{src} "
    cmd << "--js_output_file #{dest}"
    sh cmd
  end
end

file "compiler/compiler.jar" do
  mkdir "compiler"
  cd "compiler"
  url = "http://closure-compiler.googlecode.com/files/compiler-latest.zip"
  sh "curl -O #{url}"
  sh "unzip compiler-latest.zip"
  sh "rm compiler-latest.zip"
  cd "../"
end

desc 'Copy latest plugin code to local WP install'
task :maketest do
  Rake::Task['makerelease'].invoke
  sh "rm -rf ../wordpress_local/wp-content/plugins/shareaholic"
  sh "cp -r ../shareaholic_for_wordpress ../wordpress_local/wp-content/plugins/shareaholic"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".DS_Store\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \"Thumbs.db\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".git\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".gitignore\"`"
  sh "rm -rf ../wordpress_local/wp-content/plugins/shareaholic/compiler"
end

desc 'Copy latest plugin code to local WP install - DOES NOT MINIFY'
task :makequickcopy do
  sh "rm -rf ../wordpress_local/wp-content/plugins/shareaholic"
  sh "cp -r ../shareaholic_for_wordpress ../wordpress_local/wp-content/plugins/shareaholic"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".DS_Store\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \"Thumbs.db\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".git\"`"
  sh "rm -rf `find ../wordpress_local/wp-content/plugins/shareaholic/ -name \".gitignore\"`"
  sh "rm -rf ../wordpress_local/wp-content/plugins/shareaholic/compiler"
end