import { useAssetManagerStore } from "@/store/assetManagerStore";
import { Search } from "lucide-react";

const SearchAssets = () => {

  const { searchQuery, setSearchQuery } = useAssetManagerStore(); 

  return (
    <div className="relative max-w-[500px]">
      <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
      <input
        type="search"
        placeholder="Search assets by name, source, path, or category..."
        className="w-full pl-10 pr-4 py-2 text-sm border border-input rounded-lg bg-background h-10"
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)}
      />
    </div>
  )
}

export default SearchAssets;