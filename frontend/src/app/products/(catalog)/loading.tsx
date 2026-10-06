/** Mirrors the catalogue layout so nothing shifts when products stream in. */
export default function Loading() {
  return (
    <div aria-busy="true" aria-label="Loading products">
      <div className="h-[2.375rem] bg-[#eef3f9]" />
      <div className="container-site space-y-5 pb-16 pt-16">
        <div className="h-4 w-48 animate-pulse bg-mist" />
        <div className="h-14 w-full max-w-3xl animate-pulse bg-mist" />
        <div className="h-14 w-2/3 max-w-2xl animate-pulse bg-mist" />
        <div className="h-20 w-full max-w-xl animate-pulse bg-mist" />
      </div>
      <div className="h-[4.5rem] border-y border-line" />
      <div className="container-site grid grid-cols-2 gap-x-4 gap-y-10 py-16 sm:grid-cols-3 sm:gap-x-6 md:grid-cols-4 md:gap-x-8 lg:grid-cols-5 xl:grid-cols-6">
        {Array.from({ length: 12 }, (_, index) => (
          <div key={index}>
            <div className="aspect-[2/3] animate-pulse bg-mist" />
            <div className="mt-4 h-3 w-1/2 bg-mist" />
            <div className="mt-3 h-4 w-full bg-mist" />
            <div className="mt-6 h-4 w-1/3 bg-mist" />
          </div>
        ))}
      </div>
    </div>
  );
}
