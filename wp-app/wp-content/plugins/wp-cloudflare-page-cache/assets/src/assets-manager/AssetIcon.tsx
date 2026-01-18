import { Download, FileText, Palette, Plug, Zap } from "lucide-react";

type iconProps = {
  className?: string,
  category?: string,
}

const AssetIcon = ({ className='', category='' }: iconProps) => {
  const getCategoryIcon = (category) => {
    switch(category) {
    case 'theme': return <Palette className="w-4 h-4 text-purple-600" />;
    case 'plugin': return <Plug className="w-4 h-4 text-blue-600" />;
    case 'core': return <Zap className="w-4 h-4 text-green-600" />;
    case 'custom': return <FileText className="w-4 h-4 text-orange-600" />;
    case 'external': return <Download className="w-4 h-4 text-red-600" />;
    default: return <FileText className="w-4 h-4 text-gray-600" />;
    }
  };

  return (
    <div className={className}>
      {getCategoryIcon(category)}
    </div>
  );
}

export default AssetIcon