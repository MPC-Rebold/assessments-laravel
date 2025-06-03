build_output="public_html"

# clear the previous build output
rm -rf ../../$build_output/*

# copy the new build to the output
cp -r ../public/* ../../$build_output

# copy the updated index.php
cp ./index.php ../../$build_output
