build_output="public_html"

# Resolve absolute paths
build_output_dir="$(readlink -f ../../"$build_output")"
prev_build_dir="$build_output_dir"/*
new_build_src="$(readlink -f ../public)"
index_file="$(readlink -f ./index.php)"

# Clear the previous build output
echo "Clearing previous build output at $build_output_dir"
rm -rf "$build_output_dir"/*

# Copy the new build to the output
echo "Copying new build from $new_build_src to $build_output_dir"
cp -r "$new_build_src"/* "$build_output_dir"

# Copy the updated index.php
echo "Copying updated index.php from $index_file to $build_output_dir"
cp "$index_file" "$build_output_dir"
